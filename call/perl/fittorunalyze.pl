# fittorunalyze
# (c) hannes@runalyze.de, 2014
# 
# This code uses the Garmin::FIT package to extract selected fields from one FIT file
# Based on 'fitdump' by Kiyokazu SUTO
#
# Output:
# - All "info" messages start with a '#'
# - Message informations start with a '='
# - all values follow line per line as '--- key=binary=value'

use File::Basename;

my $includePath;
BEGIN {
  $includePath = dirname(__FILE__);
}
use lib $includePath;

#use lib "./perl/";

use Garmin::FIT;

# DUMP MESSAGE
sub dump_it {
  my ($self, $desc, $v) = @_;

  print "= TYPE=$desc->{local_message_type} ";
  print 'NAME=', $desc->{message_name}, ' ' if $desc->{message_name};
  print "NUMBER=$desc->{message_number}\n";
  #$self->print_all_fields($desc, $v, indent => '  ');

  my ($indent, $FH, $skip_invalid) = @opt{qw(indent FH skip_invalid)};

  # CONFIGURATION
  $indent = '--- ';
  $skip_arrays = 0;
  $skip_invalid = 1;
  $skip_debug = 1;

  $FH=\*STDOUT if !defined $FH;
  #$FH->print($indent, 'compressed_timestamp: ', $self->named_type_value('date_time', $v->[$#$v]), "\n") if $desc->{array_length} == $#$v;

  my $i_name;

  foreach $i_name (sort {$desc->{$a} <=> $desc->{$b}} grep {/^i_/} keys %$desc) {
    my $name = $i_name;

    $name =~ s/^i_//;

    my $attr = $desc->{'a_' . $name};
    my $tname = $desc->{'t_' . $name};
    my $pname = $name;

    if (ref $attr->{switch} eq 'HASH') {
      my $t_attr = $self->switched($desc, $v, $attr->{switch});

      if (ref $t_attr eq 'HASH') {
		$attr = $t_attr;
		$tname = $attr->{type_name};
		$pname = $attr->{name};
      }
    }

    my $i = $desc->{$i_name};
    my $c = $desc->{'c_' . $name};
    my $type = $desc->{'T_' . $name};
    my $invalid = $desc->{'I_' . $name};
    my $j;

    for ($j = 0 ; $j < $c ; ++$j) {
      $v->[$i + $j] != $invalid && last;
    }

    if ($j < $c || !$skip_invalid) {
      $self->last_timestamp($v->[$i]) if $type == FIT_UINT32 && $tname eq 'date_time' && $pname eq 'timestamp';

	  $FH->print($indent, $pname, '=');

      if ($type == FIT_STRING) {
		$FH->print("\"", $self->string_value($v, $i, $c), "\"");
      }
      else {
		my $pval = $self->value_cooked($tname, $attr, $invalid, $v->[$i]);

        $FH->print($v->[$i], '=', $pval);

    if (!$skip_arrays && $c > 1) {
		  my ($j, $k);

	  	  for ($j = $i + 1, $k = $i + $c ; $j < $k ; ++$j) {
  	    	$pval = $self->value_cooked($tname, $attr, $invalid, $v->[$j]);
  	    	$FH->print(',', $pval);
	  	  }
    }

		# Additional stuff I don't understand ...
		if (!$skip_debug) {
	        $FH->print(' # ');
			$FH->print('{') if $c > 1;

			if ($c > 1) {
			  my ($j, $k);

		  	  for ($j = $i + 1, $k = $i + $c ; $j < $k ; ++$j) {
		    	$pval = $self->value_cooked($tname, $attr, $invalid, $v->[$j]);
		    	$FH->print(', ', $pval);
		    	$FH->print(' (', $v->[$j], ')') if $v->[$j] ne $pval;
		  	  }

		  	  $FH->print('} ');
			}

			$FH->print('(', $desc->{'N_' . $name}, '-', $c, '-', $type_name[$type] ne '' ? $type_name[$type] : $type);
			$FH->print(', orignal name: ', $name) if $name ne $pname;
			$FH->print(', INVALID') if $j >= $c;
			$FH->print(')');
		}
	  }

	  $FH->print("\n");
    }
  }

  print "==\n";

  1;
}

# FETCH FROM
sub fetch_from {
  my $fn = shift;
  my $obj = new Garmin::FIT;

  $obj->semicircles_to_degree(1);
  $obj->mps_to_kph(1);
  $obj->use_gmtime(1);
  $obj->file($fn);
  $obj->data_message_callback_by_name('', \&dump_it);

  unless ($obj->open) {
    print STDERR $obj->error, "\n";
    return;
  }

  my ($fsize, $proto_ver, $prof_ver, $h_extra, $h_crc_expected, $h_crc_calculated) = $obj->fetch_header;

  unless (defined $fsize) {
    print STDERR $obj->error, "\n";
    $obj->close;
    return;
  }

  print "SUCCESS\n";

  # Show header information
  my ($proto_major, $proto_minor) = $obj->protocol_version_major($proto_ver);
  my ($prof_major, $prof_minor) = $obj->profile_version_major($prof_ver);

  printf "# File size: %lu, protocol version: %u.%02u, profile_version: %u.%02u\n", $fsize, $proto_major, $proto_minor, $prof_major, $prof_minor;

  if ($h_extra ne '') {
    print "# Extra octets in file header:";

    my ($i, $n);

    for ($i = 0, $n = length($h_extra) ; $i < $n ; ++$i) {
      print "  " if !($i % 16);
      print ' ' if !($i % 4);
      printf " %02x", ord(substr($h_extra, $i, 1));
    }

    print "\n";
  }

  if (defined $h_crc_calculated) {
    printf "# File header CRC: expected=0x%04X, calculated=0x%04X\n", $h_crc_expected, $h_crc_calculated;
  }

  1 while $obj->fetch;

  print STDERR $obj->error, "\n" if !$obj->EOF;
  printf "# CRC: expected=0x%04X, calculated=0x%04X\n", $obj->crc_expected, $obj->crc;

  my $garbage_size = $obj->trailing_garbages;

  print "# Trainling $garbage_size octets garbages skipped\n" if $garbage_size > 0;
  $obj->close;
}

if (@ARGV) {
  &fetch_from($ARGV[0]);
}
else {
  &fetch_from('-');
}

1;
__END__