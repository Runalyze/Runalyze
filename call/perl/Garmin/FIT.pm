package Garmin::FIT;

use FileHandle;
use POSIX qw(BUFSIZ);
use Time::Local;

BEGIN {
  $uint64_invalid = undef;

  eval {
    $uint64_invalid = unpack('Q', pack('a', -1));
  };

  unless (defined $uint64_invalid) {
    require Math::BigInt;
    import Math::BigInt;
  }
}

require Exporter;
@ISA = qw(Exporter);

@EXPORT = qw(
	     FIT_ENUM
	     FIT_SINT8
	     FIT_UINT8
	     FIT_SINT16
	     FIT_UINT16
	     FIT_SINT32
	     FIT_UINT32
	     FIT_SINT64
	     FIT_UINT64
	     FIT_STRING
	     FIT_FLOAT32
	     FIT_FLOAT64
	     FIT_UINT8Z
	     FIT_UINT16Z
	     FIT_UINT32Z
	     FIT_UINT64Z
	     FIT_BYTE
	     FIT_BASE_TYPE_MAX
	     FIT_HEADER_LENGTH
	     );

$version = 0.23;
$version_major_scale = 100;

sub version_major {
  my ($self, $ver) = @_;
  my $iver = int($ver);

  if (wantarray) {
    ($iver, int(($ver - $iver) * $version_major_scale));
  }
  else {
    $iver;
  }
}

sub version_minor {
  my ($self, $ver) = @_;

  if (wantarray) {
    (int(($ver - int($ver)) * $version_major_scale), int($ver));
  }
  else {
    int(($ver - int($ver)) * $version_major_scale);
  }
}

@version = &version_major(undef, $version);

sub version_string {
  my $self = shift;

  sprintf '%u.%02u', @_ ? $self->version_major(@_) : $self->version_major($version);
}

sub version {
  if (wantarray) {
    @version;
  }
  else {
    $version;
  }
}

$my_endian = unpack('L', pack('N', 1)) == 1 ? 1 : 0;

sub my_endian {
  $my_endian;
}

sub verbose {
  my $self = shift;

  if (@_) {
    $self->{verbose} = $_[0];
  }
  else {
    $self->{verbose};
  }
}

$protocol_version_major_shift = 4;
$protocol_version_minor_mask = (1 << $protocol_version_major_shift) - 1;

sub protocol_version_major {
  my ($self, $ver) = @_;

  if (wantarray) {
    ($ver >> $protocol_version_major_shift, $ver & $protocol_version_minor_mask);
  }
  else {
    $ver >> $protocol_version_major_shift;
  }
}

sub protocol_version_minor {
  my ($self, $ver) = @_;

  if (wantarray) {
    ($ver & $protocol_version_minor_mask, $ver >> $protocol_version_major_shift);
  }
  else {
    $ver & $protocol_version_minor_mask;
  }
}

sub protocol_version_from_string {
  my ($self, $s) = @_;
  my ($major, $minor) = split /\./, $s, 2;

  if (wantarray) {
    ($major + 0, $minor & $protocol_version_minor_mask);
  }
  else {
    ($major << $protocol_version_major_shift) | ($minor & $protocol_version_minor_mask);
  }
}

$protocol_version = &protocol_version_from_string(undef, "2.2");
@protocol_version = &protocol_version_major(undef, $protocol_version);
$protocol_version_header_crc_started = &protocol_version_from_string(undef, "1.0");

sub protocol_version_string {
  my $self = shift;

  sprintf '%u.%u', @_ ? $self->protocol_version_major(@_) : $self->protocol_version_major($protocol_version);
}

sub protocol_version {
  if (wantarray) {
    @protocol_version;
  }
  else {
    $protocol_version;
  }
}

$profile_version_scale = 100;

sub profile_version_major {
  my ($self, $ver) = @_;

  if (wantarray) {
    (int($ver / $profile_version_scale), $ver % $profile_version_scale);
  }
  else {
    int($ver / $profile_version_scale);
  }
}

sub profile_version_minor {
  my ($self, $ver) = @_;

  if (wantarray) {
    ($ver % $profile_version_scale, int($ver / $profile_version_scale));
  }
  else {
    $ver % $profile_version_scale;
  }
}

sub profile_version_from_string {
  my ($self, $s) = @_;
  my ($major, $minor) = split /\./, $s, 2;

  if (wantarray) {
    ($major + 0, $minor & $profile_version_minor_mask);
  }
  else {
    $major * $profile_version_scale + $minor % $profile_version_scale;
  }
}

$profile_version = &profile_version_from_string(undef, "20.08");
@profile_version = &profile_version_major(undef, $profile_version);

sub profile_version_string {
  my $self = shift;

  sprintf '%u.%02u', @_ ? $self->profile_version_major(@_) : $self->profile_version_major($profile_version);
}

sub profile_version {
  if (wantarray) {
    @profile_version;
  }
  else {
    $profile_version;
  }
}

# CRC calculation routine taken from
#   Haruhiko Okumura, C gengo ni yoru algorithm dai jiten (1st ed.), GijutsuHyouronsha 1991.

my $crc_poly = 2 ** 16 + 2 ** 15 + 2 ** 2 + 2 ** 0; # CRC-16
my ($crc_poly_deg, $crc_poly_rev);
my ($x, $y, $i);

for ($crc_poly_deg = 0, $x = $crc_poly ; $x >>= 1 ;) {
  ++$crc_poly_deg;
}

$crc_octets = int($crc_poly_deg / 8 + 0.5);

for ($crc_poly_rev = 0, $y = 1, $x = 2 ** ($crc_poly_deg - 1) ; $x ;) {
  $crc_poly_rev |= $y if $x & $crc_poly;
  $y <<= 1;
  $x >>= 1;
}

@crc_table = ();

for ($i = 0 ; $i < 2 ** 8 ; ++$i) {
  my $r = $i;
  my $j;

  for ($j = 0 ; $j < 8 ; ++$j) {
    if ($r & 1) {
      $r = ($r >> 1) ^ $crc_poly_rev;
    }
    else {
      $r >>= 1;
    }
  }

  $crc_table[$i] = $r;
}

sub dump {
  my ($self, $s, $FH) = @_;
  my ($i, $d);

  for ($i = 0 ; $i < length($s) ;) {
    $FH->printf(' %03u', ord(substr($s, $i++, 1)));
  }
}

sub safe_isa {
  eval {$_[0]->isa($_[1])};
}

sub error_callback {
  my $self = shift;

  if (@_) {
    if (&safe_isa($_[0], CODE)) {
      $self->{error_callback_argv} = [@_[1 .. $#_]];
      $self->{error_callback} = $_[0];
    }
    else {
      undef;
    }
  }
  else {
    $self->{error_callback};
  }
}

sub error {
  my $self = shift;

  if (@_) {
    my ($p, $fn, $l, $subr, $fit);

    (undef, $fn, $l) = caller(0);
    ($p, undef, undef, $subr) = caller(1);
    $fit = $self->file;
    $fit .= ': ' if $fit ne '';

    $self->{error} = "$p::$subr\#$l\@$fn: $fit$_[0]";

    if (&safe_isa($self->{error_callback}, CODE)) {
      my $argv = &safe_isa($self->{error_callback_argv}, ARRAY) ? $self->{error_callback_argv} : [];

      $self->{error_callback}->($self, @$argv);
    }
    else {
      undef;
    }
  }
  else {
    $self->{error};
  }
}

sub file_read {
  my $self = shift;

  if (@_) {
    $self->{file_read} = $_[0];
  }
  else {
    $self->{file_read};
  }
}

sub file_size {
  my $self = shift;

  if (@_) {
    $self->{file_size} = $_[0];
  }
  else {
    $self->{file_size};
  }
}

sub file_processed {
  my $self = shift;

  if (@_) {
    $self->{file_processed} = $_[0];
  }
  else {
    $self->{file_processed};
  }
}

sub crc {
  my $self = shift;

  if (@_) {
    $self->{crc} = $_[0];
  }
  else {
    $self->{crc};
  }
}

sub offset {
  my $self = shift;

  if (@_) {
    $self->{offset} = $_[0];
  }
  else {
    $self->{offset};
  }
}

sub buffer {
  my $self = shift;

  if (@_) {
    $self->{buffer} = $_[0];
  }
  else {
    $self->{buffer};
  }
}

sub crc_of_string {
  my ($self, $crc, $p, $b, $n) = @_;
  my $e = $b + $n;

  while ($b < $e) {
    $crc = ($crc >> 8) ^ $crc_table[($crc & (2 ** 8 - 1)) ^ ord(substr($$p, $b++, 1))];
  }

  $crc;
}

sub crc_calc {
  my ($self, $m) = @_;
  my $over = $self->file_read - $self->file_size;

  $over = 0 if $over < 0;

  if ($m > $over) {
    my $buffer = $self->buffer;

    $self->crc($self->crc_of_string($self->crc, $buffer, length($$buffer) - $m, $m - $over));
  }
}

sub crc_expected {
  my $self = shift;

  if (@_) {
    $self->{crc_expected} = $_[0];
  }
  else {
    $self->{crc_expected};
  }
}

sub trailing_garbages {
  my $self = shift;

  if (@_) {
    $self->{trailing_garbages} = $_[0];
  }
  else {
    $self->{trailing_garbages};
  }
}

sub maybe_chained {
  my $self = shift;

  if (@_) {
    $self->{maybe_chained} = $_[0];
  }
  else {
    $self->{maybe_chained};
  }
}

sub really_clear_buffer {
  my $self = shift;
  my $buffer = $self->{buffer};

  $self->crc_calc(length($$buffer)) if !defined $self->crc;
  $self->file_processed($self->file_processed + $self->offset);
  substr($$buffer, 0, $self->offset) = '';
  $self->offset(0);
}

sub cp_fit {
  my $self = shift;

  if (@_) {
    $self->{cp_fit} = $_[0];
  }
  else {
    $self->{cp_fit};
  }
}

sub cp_fit_FH {
  my $self = shift;

  if (@_) {
    $self->{cp_fit_FH} = $_[0];
  }
  else {
    $self->{cp_fit_FH};
  }
}

sub clear_buffer {
  my $self = shift;

  if ($self->offset > 0) {
    if ($self->cp_fit) {
      my $FH = $self->cp_fit_FH;

      if (&safe_isa($FH, 'FileHandle') && $FH->opened) {
	my $buffer = $self->buffer;

	$FH->print(substr($$buffer, 0, $self->offset));
	$FH->flush;
	$self->really_clear_buffer;
      }
    }
    else {
      $self->really_clear_buffer;
    }
  }
}

sub file {
  my $self = shift;

  if (@_) {
    $self->{file} = $_[0];
  }
  else {
    $self->{file};
  }
}

sub FH {
  my $self = shift;

  if (@_) {
    $self->{FH} = $_[0];
  }
  else {
    $self->{FH};
  }
}

sub EOF {
  my $self = shift;

  if (@_) {
    $self->{EOF} = $_[0];
  }
  else {
    $self->{EOF};
  }
}

sub end_of_chunk {
  my $self = shift;

  if (@_) {
    $self->{end_of_chunk} = $_[0];
  }
  else {
    $self->{end_of_chunk};
  }
}

sub fill_buffer {
  my ($self, $req) = @_;
  my $buffer = $self->buffer;
  my $FH = $self->FH;

  while (length($$buffer) - $self->offset < $req) {
    $self->clear_buffer;

    my $n = $FH->read($$buffer, BUFSIZ, length($$buffer));

    if ($n > 0) {
      $self->file_read($self->file_read + $n);

      if (defined $self->file_size) {
	if (defined $self->crc) {
	  $self->crc_calc($n);
	}
	else {
	  $self->crc_calc(length($$buffer));
	}
      }
    }
    else {
      if (defined $n) {
	$self->error("unexpected EOF");
	$self->EOF(1);
      }
      else {
	$self->error("read(FH): $!");
      }

      return undef;
    }
  }

  1;
}

$header_template = 'C C v V V';
$header_length = length(pack($header_template));

sub FIT_HEADER_LENGTH {
  $header_length;
}

$FIT_signature_string = '.FIT';
$FIT_signature = unpack('V', $FIT_signature_string);

$header_crc_template = 'v';
$header_crc_length = length(pack($header_crc_template));

sub fetch_header {
  my $self = shift;

  $self->fill_buffer($header_length) || return undef;

  my $buffer = $self->buffer;
  my $h_min = substr($$buffer, $self->offset, $header_length);
  my ($h_len, $proto_ver, $prof_ver, $f_len, $sig) = unpack($header_template, $h_min);

  $self->offset($self->offset + $header_length);

  if ($h_len < $header_length) {
    $self->error("not a .FIT header ($h_len < $header_length)");
    ();
  }
  else {
    my $extra;

    if ($h_len > $header_length) {
      $self->fill_buffer($h_len - $header_length) || return undef;
      $extra = substr($$buffer, $self->offset, $h_len - $header_length);
      $self->offset($self->offset + $h_len - $header_length);
    }

    if ($sig != $FIT_signature) {
      $self->error("not a .FIT header (" .
		   join('', map {($_ ne "\\" && 0x20 >= ord($_) && ord($_) <= 0x7E) ? $_ : sprintf("\\x%02X", ord($_))} split //, pack('V', $sig))
		   . " ne '$FIT_signature_string')");
      ();
    }
    else {
      my ($crc_expected, $crc_calculated);

      if ($proto_ver >= $protocol_version_header_crc_started && length($extra) >= $header_crc_length) {
	$crc_expected = unpack($header_crc_template, substr($extra, -$header_crc_length));
	substr($extra, -$header_crc_length) = '';
	$crc_calculated = $self->crc_of_string(0, \$h_min, 0, $header_length);
      }

      my $f_size = $f_len + $h_len;

      $self->file_size($f_size);

      unless (defined $self->crc) {
	$self->crc(0);
	$self->crc_calc(length($$buffer));
      }

      ($f_size, $proto_ver, $prof_ver, $extra, $crc_expected, $crc_calculated);
    }
  }
}

sub cat_header {
  my ($self, $proto_ver, $prof_ver, $f_len, $p, $p_extra) = @_;

  if (!defined $p) {
    my $bin = '';

    $p = \$bin;
  }

  my $h_len = $header_length;

  ref $p_extra eq 'SCALAR' and $h_len += length($$p_extra);
  $proto_ver >= $protocol_version_header_crc_started and $h_len += $header_crc_length;

  my $h_start = length($$p);

  $$p .= pack($header_template, $h_len, $proto_ver, $prof_ver, $f_len, $FIT_signature);
  ref $p_extra eq 'SCALAR' and $$p .= $$p_extra;

  $proto_ver >= $protocol_version_header_crc_started and
    $$p .= pack($header_crc_template, $self->crc_of_string(0, $p, $h_start, length($$p) - $h_start));

  $p;
}

sub FIT_ENUM() {0;}
sub FIT_SINT8() {1;}
sub FIT_UINT8() {2;}
sub FIT_SINT16() {3;}
sub FIT_UINT16() {4;}
sub FIT_SINT32() {5;}
sub FIT_UINT32() {6;}
sub FIT_STRING() {7;}
sub FIT_FLOAT32() {8;}
sub FIT_FLOAT64() {9;}
sub FIT_UINT8Z() {10;}
sub FIT_UINT16Z() {11;}
sub FIT_UINT32Z() {12;}
sub FIT_BYTE() {13;}
sub FIT_SINT64() {14;}
sub FIT_UINT64() {15;}
sub FIT_UINT64Z() {16;}
sub FIT_BASE_TYPE_MAX() {FIT_UINT64Z;}

$rechd_offset_compressed_timestamp_header = 7;
$rechd_mask_compressed_timestamp_header = 1 << $rechd_offset_compressed_timestamp_header;
$rechd_offset_cth_local_message_type = 5;
$rechd_length_cth_local_message_type = 2;
$rechd_mask_cth_local_message_type = ((1 << $rechd_length_cth_local_message_type) - 1) << $rechd_offset_cth_local_message_type;
$rechd_length_cth_timestamp = $rechd_offset_cth_local_message_type;
$rechd_mask_cth_timestamp = (1 << $rechd_length_cth_timestamp) - 1;
$rechd_offset_definition_message = 6;
$rechd_mask_definition_message = 1 << $rechd_offset_definition_message;
$rechd_offset_devdata_message = 5;
$rechd_mask_devdata_message = 1 << $rechd_offset_devdata_message;
$rechd_length_local_message_type = 4;
$rechd_mask_local_message_type = (1 << $rechd_length_local_message_type) - 1;
$cthd_offset_local_message_type = 5;
$cthd_length_local_message_type = 2;
$cthd_mask_local_message_type = (1 << $cthd_length_local_message_type) - 1;
$cthd_length_time_offset = 5;
$cthd_mask_time_offset = (1 << $cthd_length_time_offset) - 1;

$defmsg_min_template = 'C C C S C';
$defmsg_min_length = length(pack($defmsg_min_template));

$deffld_template = 'C C C';
$deffld_length = length(pack($deffld_template));
$deffld_mask_endian_p = 1 << 7;
$deffld_mask_type = (1 << 5) - 1;

$devdata_min_template = 'C';
$devdata_min_length = length(pack($devdata_min_template));
$devdata_deffld_template = 'C C C';
$devdata_deffld_length = length(pack($deffld_template));

@invalid = (0xFF) x ($deffld_mask_type + 1);

$invalid[FIT_SINT8] = 0x7F;
$invalid[FIT_SINT16] = 0x7FFF;
$invalid[FIT_UINT16] = 0xFFFF;
$invalid[FIT_SINT32] = 0x7FFFFFFF;
$invalid[FIT_UINT32] = 0xFFFFFFFF;
$invalid[FIT_STRING] = $invalid[FIT_UINT8Z] = $invalid[FIT_UINT16Z] = $invalid[FIT_UINT32Z] = $invalid[FIT_UINT64Z] = 0;
#$invalid[FIT_FLOAT32] = NaN;
#$invalid[FIT_FLOAT64] = NaN;
$invalid[FIT_FLOAT32] = unpack('f', pack('V', 0xFFFFFFFF));
$invalid[FIT_FLOAT64] = unpack('d', pack('V V', 0xFFFFFFFF, 0xFFFFFFFF));

my ($big_int_base32, $sint64_2c_mask, $sint64_2c_base, $sint64_2c_sign);

if (defined $uint64_invalid) {
  $invalid[FIT_UINT64] = $uint64_invalid;
  $invalid[FIT_SINT64] = eval '0x7FFFFFFFFFFFFFFF';
}
else {
  $invalid[FIT_UINT64] = Math::BigInt->new('0xFFFFFFFFFFFFFFFF');
  $invalid[FIT_SINT64] = Math::BigInt->new('0x7FFFFFFFFFFFFFFF');
  $big_int_base32 = Math::BigInt->new('0x100000000');
  $sint64_2c_mask = Math::BigInt->new('0xFFFFFFFFFFFFFFFF');
  $sint64_2c_base = Math::BigInt->new('0x10000000000000000');
  $sint64_2c_sign = Math::BigInt->new('0x1000000000000000');
}

sub packfilter_uint64_big_endian {
  my @res = $_[0]->bdiv($big_int_base32);

  @res;
}

sub packfilter_uint64_little_endian {
  my @res = $_[0]->bdiv($big_int_base32);

  @res[1, 0];
}

*packfilter_uint64 = $my_endian ? \&packfilter_uint64_big_endian : \&packfilter_uint64_little_endian;

sub unpackfilter_uint64_big_endian {
  my ($hi, $lo) = @_;

  Math::BigInt->new($hi)->blsft(32)->badd($lo);
}

sub unpackfilter_uint64_little_endian {
  &unpackfilter_uint64_big_endian(@_[1, 0]);
}

*unpackfilter_uint64 = $my_endian ? \&unpackfilter_uint64_big_endian : \&unpackfilter_uint64_little_endian;

sub packfilter_sint64_big_endian {
  if ($_[0]->bcmp(0) < 0) {
    &packfilter_uint64_big_endian($sint64_2c_mask->band($sint64_2c_base->badd($_[0])));
  }
  else {
    &packfilter_uint64_big_endian($_[0]);
  }
}

sub packfilter_sint64_little_endian {
  if ($_[0]->bcmp(0) < 0) {
    &packfilter_uint64_little_endian($sint64_2c_mask->band($sint64_2c_base->badd($_[0])));
  }
  else {
    &packfilter_uint64_little_endian($_[0]);
  }
}

*packfilter_sint64 = $my_endian ? \&packfilter_sint64_big_endian : \&packfilter_sint64_little_endian;

sub unpackfilter_sint64_big_endian {
  my ($hi, $lo) = @_;
  my $n = Math::BigInt->new($hi)->blsft(32)->badd($lo)->band($sint64_2c_mask);

  if ($n->band($sint64_2c_sign)->bcmp(0) == 0) {
    $n;
  }
  else {
    $n->bsub($sint64_2c_base);
  }
}

sub unpackfilter_sint64_little_endian {
  &unpackfilter_sint64_big_endian(@_[1, 0]);
}

*unpackfilter_sint64 = $my_endian ? \&unpackfilter_sint64_big_endian : \&unpackfilter_sint64_little_endian;

sub invalid {
  my ($self, $type) = @_;

  $invalid[$type & $deffld_mask_type];
}

@size = (1) x ($deffld_mask_type + 1);

$size[FIT_SINT16] = $size[FIT_UINT16] = $size[FIT_UINT16Z] = 2;
$size[FIT_SINT32] = $size[FIT_UINT32] = $size[FIT_UINT32Z] = $size[FIT_FLOAT32] = 4;
$size[FIT_FLOAT64] = $size[FIT_SINT64] = $size[FIT_UINT64] = $size[FIT_UINT64Z] = 8;

@template = ('C') x ($deffld_mask_type + 1);
@packfactor = (1) x ($deffld_mask_type + 1);
@packfilter = (undef) x ($deffld_mask_type + 1);
@unpackfilter = (undef) x ($deffld_mask_type + 1);

$template[FIT_SINT8] = 'c';
$template[FIT_SINT16] = 's';
$template[FIT_UINT16] = $template[FIT_UINT16Z] = 'S';
$template[FIT_SINT32] = 'l';
$template[FIT_UINT32] = $template[FIT_UINT32Z] = 'L';
$template[FIT_FLOAT32] = 'f';
$template[FIT_FLOAT64] = 'd';

if (defined $uint64_invalid) {
  $template[FIT_SINT64] = 'q';
  $template[FIT_UINT64] = $template[FIT_UINT64Z] = 'Q';
}
else {
  $template[FIT_SINT64] = $template[FIT_UINT64] = $template[FIT_UINT64Z] = 'L';
  $packfactor[FIT_SINT64] = $packfactor[FIT_UINT64] = $packfactor[FIT_UINT64Z] = 2;
  $packfilter[FIT_SINT64] = \&packfiltr_sint64;
  $unpackfilter[FIT_SINT64] = \&unpackfiltr_sint64;
  $packfilter[FIT_UINT64] = $packfilter[FIT_UINT64Z] = \&packfiltr_uint64;
  $unpackfilter[FIT_UINT64] = $unpackfilter[FIT_UINT64Z] = \&unpackfiltr_uint64;
}

%named_type =
  (

   'file' => +{
     '_base_type' => FIT_ENUM,
     'device' => 1,
     'settings' => 2,
     'sport' => 3,
     'activity' => 4,
     'workout' => 5,
     'course' => 6,
     'schedules' => 7,
     'weight' => 9,
     'totals' => 10,
     'goals' => 11,
     'blood_pressure' => 14,
     'monitoring_a' => 15,
     'activity_summary' => 20,
     'monitoring_daily' => 28,
     'monitoring_b' => 32,
     'segment' => 34,
     'segment_list' => 35,
     'exd_configuration' => 40,
     'mfg_range_min' => 0xF7,
     'mfg_range_max' => 0xFE,
   },

   'mesg_num' => +{
     '_base_type' => FIT_UINT16,
     'file_id' => 0,
     'capabilities' => 1,
     'device_settings' => 2,
     'user_profile' => 3,
     'hrm_profile' => 4,
     'sdm_profile' => 5,
     'bike_profile' => 6,
     'zones_target' => 7,
     'hr_zone' => 8,
     'power_zone' => 9,
     'met_zone' => 10,
     'sport' => 12,
#    'unknown13' => 13, # unknown
     'goal' => 15,
     'session' => 18,
     'lap' => 19,
     'record' => 20,
     'event' => 21,
     'source' => 22, # undocumented
     'device_info' => 23,
     'workout' => 26,
     'workout_step' => 27,
     'schedule' => 28,
     'location' => 29, # undocumented
     'weight_scale' => 30,
     'course' => 31,
     'course_point' => 32,
     'totals' => 33,
     'activity' => 34,
     'software' => 35,
     'file_capabilities' => 37,
     'mesg_capabilities' => 38,
     'field_capabilities' => 39,
     'file_creator' => 49,
     'blood_pressure' => 51,
     'speed_zone' => 53,
     'monitoring' => 55,
     'training_file' => 72,
     'hrv' => 78,
#    'unknown79' => 79, # unknown
     'ant_rx' => 80,
     'ant_tx' => 81,
     'ant_channel_id' => 82,
     'length' => 101,
     'monitoring_info' => 103,
     'battery' => 104, # undocumented
     'pad' => 105,
     'slave_device' => 106,
#    'unknown113' => 113, # unknown
     'connectivity' => 127,
     'weather_conditions' => 128,
     'weather_alert' => 129,
     'cadence_zone' => 131,
     'hr' => 132,
#    'unknown140' => 140, # unknown
     'segment_lap' => 142,
     'memo_glob' => 145,
     'sensor' => 147, # undocumented
     'segment_id' => 148,
     'segment_leaderboard_entry' => 149,
     'segment_point' => 150,
     'segment_file' => 151,
     'gps_metadata' => 160,
     'camera_event' => 161,
     'timestamp_correlation' => 162,
     'gyroscope_data' => 164,
     'accelerometer_data' => 165,
     'three_d_sensor_calibration' => 167,
     'video_frame' => 169,
     'obdii_data' => 174,
     'nmea_sentence' => 177,
     'aviation_attitude' => 178,
     'video' => 184,
     'video_title' => 185,
     'video_description' => 186,
     'video_clip' => 187,
     'exd_screen_configuration' => 200,
     'exd_data_field_configuration' => 201,
     'exd_data_concept_configuration' => 202,
     'field_description' => 206,
     'developer_data_id' => 207,
     'magnetometer_data' => 208,
     'mfg_range_min' => 0xFF00,
     'mfg_range_max' => 0xFFFE,
   },

   'checksum' => +{
     '_base_type' => FIT_UINT8,
     'clear' => 0,
     'ok' => 1,
   },

   'file_flags' => +{
     '_base_type' => FIT_UINT8Z,
     '_mask' => 1,
     'read' => 0x02,
     'write' => 0x04,
     'erase' => 0x08,
   },

   'mesg_count' => +{
     '_base_type' => FIT_ENUM,
     'num_per_file' => 0,
     'max_per_file' => 1,
     'max_per_file_type' => 2,
   },

   'date_time' => +{
     '_base_type' => FIT_UINT32,
     'min' => 0x10000000,
     '_min' => 0x10000000,
     '_out_of_range' => 'seconds from device power on',
     '_offset' => -timegm(0, 0, 0, 31, 11, 1989), # 1989-12-31 00:00:00 GMT
   },

   'local_date_time' => +{
     '_base_type' => FIT_UINT32,
     'min' => 0x10000000,
   },

   'message_index' => +{
     '_base_type' => FIT_UINT16,
     '_mask' => 1,
     'selected' => 0x8000,
     'reserved' => 0x7000,
     'mask' => 0x0FFF,
   },

   'device_index' => +{ # dynamically created, as devices are added
     '_base_type' => FIT_UINT8,
     'device0' => 0, # local, v6.00, garmin prod. edge520 (2067)
     'device1' => 1, # local, v6.00, garmin prod. edge520 (2067)
     'device2' => 2, # local, v3.00, garmin prod. 1619
     'heart_rate' => 3, # antplus
     'speed' => 4, # antplus
     'cadence' => 5, # antplus
     'device6' => 6, # antplus power?
   },

   'gender' => +{
     '_base_type' => FIT_ENUM,
     'female' => 0,
     'male' => 1,
   },

   'language' => +{
     '_base_type' => FIT_ENUM,
     'english' => 0,
     'french' => 1,
     'italian' => 2,
     'german' => 3,
     'spanish' => 4,
     'croatian' => 5,
     'czech' => 6,
     'danish' => 7,
     'dutch' => 8,
     'finnish' => 9,
     'greek' => 10,
     'hungarian' => 11,
     'norwegian' => 12,
     'polish' => 13,
     'portuguese' => 14,
     'slovakian' => 15,
     'slovenian' => 16,
     'swedish' => 17,
     'russian' => 18,
     'turkish' => 19,
     'latvian' => 20,
     'ukrainian' => 21,
     'arabic' => 22,
     'farsi' => 23,
     'bulgarian' => 24,
     'romanian' => 25,
     'chinese' => 26,
     'japanese' => 27,
     'korean' => 28,
     'taiwanese' => 29,
     'thai' => 30,
     'hebrew' => 31,
     'brazilian_portuguese' => 32,
     'indonesian' => 33,
     'custom' => 254,
   },

   'language_bits_0' => +{
     '_base_type' => FIT_UINT8Z,
     'english' => 0x01,
     'french' => 0x02,
     'italian' => 0x04,
     'german' => 0x08,
     'spanish' => 0x10,
     'croatian' => 0x20,
     'czech' => 0x40,
     'danish' => 0x80,
   },

   'language_bits_1' => +{
     '_base_type' => FIT_UINT8Z,
     'dutch' => 0x01,
     'finnish' => 0x02,
     'greek' => 0x04,
     'hungarian' => 0x08,
     'norwegian' => 0x10,
     'polish' => 0x20,
     'portuguese' => 0x40,
     'slovakian' => 0x80,
   },

   'language_bits_2' => +{
     '_base_type' => FIT_UINT8Z,
     'slovenian' => 0x01,
     'swedish' => 0x02,
     'russian' => 0x04,
     'turkish' => 0x08,
     'latvian' => 0x10,
     'ukrainian' => 0x20,
     'arabic' => 0x40,
     'farsi' => 0x80,
   },

   'language_bits_3' => +{
     '_base_type' => FIT_UINT8Z,
     'bulgarian' => 0x01,
     'romanian' => 0x02,
     'chinese' => 0x04,
     'japanese' => 0x08,
     'korean' => 0x10,
     'taiwanese' => 0x20,
     'thai' => 0x40,
     'hebrew' => 0x80,
   },

   'language_bits_4' => +{
     '_base_type' => FIT_UINT8Z,
     'brazilian_portuguese' => 0x01,
     'indonesian' => 0x02,
   },

   'time_zone' => +{
     '_base_type' => FIT_ENUM,
     'almaty' => 0,
     'bangkok' => 1,
     'bombay' => 2,
     'brasilia' => 3,
     'cairo' => 4,
     'cape_verde_is' => 5,
     'darwin' => 6,
     'eniwetok' => 7,
     'fiji' => 8,
     'hong_kong' => 9,
     'islamabad' => 10,
     'kabul' => 11,
     'magadan' => 12,
     'mid_atlantic' => 13,
     'moscow' => 14,
     'muscat' => 15,
     'newfoundland' => 16,
     'samoa' => 17,
     'sydney' => 18,
     'tehran' => 19,
     'tokyo' => 20,
     'us_alaska' => 21,
     'us_atlantic' => 22,
     'us_central' => 23,
     'us_eastern' => 24,
     'us_hawaii' => 25,
     'us_mountain' => 26,
     'us_pacific' => 27,
     'other' => 28,
     'auckland' => 29,
     'kathmandu' => 30,
     'europe_western_wet' => 31,
     'europe_central_cet' => 32,
     'europe_eastern_eet' => 33,
     'jakarta' => 34,
     'perth' => 35,
     'adelaide' => 36,
     'brisbane' => 37,
     'tasmania' => 38,
     'iceland' => 39,
     'amsterdam' => 40,
     'athens' => 41,
     'barcelona' => 42,
     'berlin' => 43,
     'brussels' => 44,
     'budapest' => 45,
     'copenhagen' => 46,
     'dublin' => 47,
     'helsinki' => 48,
     'lisbon' => 49,
     'london' => 50,
     'madrid' => 51,
     'munich' => 52,
     'oslo' => 53,
     'paris' => 54,
     'prague' => 55,
     'reykjavik' => 56,
     'rome' => 57,
     'stockholm' => 58,
     'vienna' => 59,
     'warsaw' => 60,
     'zurich' => 61,
     'quebec' => 62,
     'ontario' => 63,
     'manitoba' => 64,
     'saskatchewan' => 65,
     'alberta' => 66,
     'british_columbia' => 67,
     'boise' => 68,
     'boston' => 69,
     'chicago' => 70,
     'dallas' => 71,
     'denver' => 72,
     'kansas_city' => 73,
     'las_vegas' => 74,
     'los_angeles' => 75,
     'miami' => 76,
     'minneapolis' => 77,
     'new_york' => 78,
     'new_orleans' => 79,
     'phoenix' => 80,
     'santa_fe' => 81,
     'seattle' => 82,
     'washington_dc' => 83,
     'us_arizona' => 84,
     'chita' => 85,
     'ekaterinburg' => 86,
     'irkutsk' => 87,
     'kaliningrad' => 88,
     'krasnoyarsk' => 89,
     'novosibirsk' => 90,
     'petropavlovsk_kamchatskiy' => 91,
     'samara' => 92,
     'vladivostok' => 93,
     'mexico_central' => 94,
     'mexico_mountain' => 95,
     'mexico_pacific' => 96,
     'cape_town' => 97,
     'winkhoek' => 98,
     'lagos' => 99,
     'riyahd' => 100,
     'venezuela' => 101,
     'australia_lh' => 102,
     'santiago' => 103,
     'manual' => 253,
     'automatic' => 254,
   },

   'display_measure' => +{
     '_base_type' => FIT_ENUM,
     'metric' => 0,
     'statue' => 1,
   },

   'display_heart' => +{
     '_base_type' => FIT_ENUM,
     'bpm' => 0,
     'max' => 1,
     'reserve' => 2,
   },

   'display_power' => +{
     '_base_type' => FIT_ENUM,
     'watts' => 0,
     'percent_ftp' => 1,
   },

   'display_position' => +{
     '_base_type' => FIT_ENUM,
     'degree' => 0,
     'degree_minute' => 1,
     'degree_minute_second' => 2,
     'austrian_grid' => 3,
     'british_grid' => 4,
     'dutch_grid' => 5,
     'hungarian_grid' => 6,
     'finnish_grid' => 7,
     'german_grid' => 8,
     'icelandic_grid' => 9,
     'indonesian_equatorial' => 10,
     'indonesian_irian' => 11,
     'indonesian_southern' => 12,
     'india_zone_0' => 13,
     'india_zone_IA' => 14,
     'india_zone_IB' => 15,
     'india_zone_IIA' => 16,
     'india_zone_IIB' => 17,
     'india_zone_IIIA' => 18,
     'india_zone_IIIB' => 19,
     'india_zone_IVA' => 20,
     'india_zone_IVB' => 21,
     'irish_transverse' => 22,
     'irish_grid' => 23,
     'loran' => 24,
     'maidenhead_grid' => 25,
     'mgrs_grid' => 26,
     'new_zealand_grid' => 27,
     'new_zealand_transverse' => 28,
     'qatar_grid' => 29,
     'modified_swedish_grid' => 30,
     'swedish_grid' => 31,
     'south_african_grid' => 32,
     'swiss_grid' => 33,
     'taiwan_grid' => 34,
     'united_states_grid' => 35,
     'utm_ups_grid' => 36,
     'west_malayan' => 37,
     'borneo_rso' => 38,
     'estonian_grid' => 39,
     'latvian_grid' => 40,
     'swedish_ref_99_grid' => 41,
   },

   'sport' => +{
     '_base_type' => FIT_ENUM,
     'generic' => 0,
     'running' => 1,
     'cycling' => 2,
     'transition' => 3,
     'fitness_equipment' => 4,
     'swimming' => 5,
     'basketball' => 6,
     'soccer' => 7,
     'tennis' => 8,
     'american_football' => 9,
     'training' => 10,
     'walking' => 11,
     'cross_country_skiing' => 12,
     'alpine_skiing' => 13,
     'snowboarding' => 14,
     'rowing' => 15,
     'mountaineering' => 16,
     'hiking' => 17,
     'multisport' => 18,
     'paddling' => 19,
     'flying' => 20,
     'e_biking' => 21,
     'motorcycling' => 22,
     'boating' => 23,
     'driving' => 24,
     'golf' => 25,
     'hang_gliding' => 26,
     'horseback_riding' => 27,
     'hunting' => 28,
     'fishing' => 29,
     'inline_skating' => 30,
     'rock_climbing' => 31,
     'sailing' => 32,
     'ice_skating' => 33,
     'sky_diving' => 34,
     'snowshoeing' => 35,
     'snowmobiling' => 36,
     'stand_up_paddleboarding' => 37,
     'surfing' => 38,
     'wakeboarding' => 39,
     'water_skiing' => 40,
     'kayaking' => 41,
     'rafting' => 42,
     'windsurfing' => 43,
     'kitesurfing' => 44,
     'tactical' => 45,
     'jumpmaster' => 46,
     'boxing' => 47,
     'all' => 254,
   },

   'sport_bits_0' => +{
     '_base_type' => FIT_UINT8Z,
     'generic' => 0x01,
     'running' => 0x02,
     'cycling' => 0x04,
     'transition' => 0x08,
     'fitness_equipment' => 0x10,
     'swimming' => 0x20,
     'basketball' => 0x40,
     'soccer' => 0x80,
   },

   'sport_bits_1' => +{
     '_base_type' => FIT_UINT8Z,
     'tennis' => 0x01,
     'american_football' => 0x02,
     'training' => 0x04,
     'walking' => 0x08,
     'cross_country_skiing' => 0x10,
     'alpine_skiing' => 0x20,
     'snowboarding' => 0x40,
     'rowing' => 0x80,
   },

   'sport_bits_2' => +{
     '_base_type' => FIT_UINT8Z,
     'mountaineering' => 0x01,
     'hiking' => 0x02,
     'multisport' => 0x04,
     'paddling' => 0x08,
     'flying' => 0x10,
     'e_biking' => 0x20,
     'motorcycling' => 0x40,
     'boating' => 0x80,
   },

   'sport_bits_3' => +{
     '_base_type' => FIT_UINT8Z,
     'driving' => 0x01,
     'golf' => 0x02,
     'hang_gliding' => 0x04,
     'horseback_riding' => 0x08,
     'hunting' => 0x10,
     'fishing' => 0x20,
     'inline_skating' => 0x40,
     'rock_climbing' => 0x80,
   },

   'sport_bits_4' => +{
     '_base_type' => FIT_UINT8Z,
     'sailing' => 0x01,
     'ice_skating' => 0x02,
     'sky_diving' => 0x04,
     'snowshoeing' => 0x08,
     'snowmobiling' => 0x10,
     'stand_up_paddleboarding' => 0x20,
     'surfing' => 0x40,
     'wakeboarding' => 0x80,
   },

   'sport_bits_5' => +{
     '_base_type' => FIT_UINT8Z,
     'water_skiing' => 0x01,
     'kayaking' => 0x02,
     'rafting' => 0x04,
     'windsurfing' => 0x08,
     'kitesurfing' => 0x10,
     'tactical' => 0x20,
     'jumpmaster' => 0x40,
     'boxing' => 0x80,
   },

   'sub_sport' => +{
     '_base_type' => FIT_ENUM,
     'generic' => 0,
     'treadmill' => 1,
     'street' => 2,
     'trail' => 3,
     'track' => 4,
     'spin' => 5,
     'indoor_cycling' => 6,
     'road' => 7,
     'mountain' => 8,
     'downhill' => 9,
     'recumbent' => 10,
     'cyclocross' => 11,
     'hand_cycling' => 12,
     'track_cycling' => 13,
     'indoor_rowing' => 14,
     'elliptical' => 15,
     'stair_climbing' => 16,
     'lap_swimming' => 17,
     'open_water' => 18,
     'flexibility_training' => 19,
     'strength_training' => 20,
     'warm_up' => 21,
     'match' => 22,
     'exercise' => 23,
     'challenge' => 24,
     'indoor_skiing' => 25,
     'cardio_training' => 26,
     'indoor_walking' => 27,
     'e_bike_fitness' => 28,
     'bmx' => 29,
     'casual_walking' => 30,
     'speed_walking' => 31,
     'bike_to_run_transition' => 32,
     'run_to_bike_transition' => 33,
     'swim_to_bike_transition' => 34,
     'atv' => 35,
     'motocross' => 36,
     'backcountry' => 37,
     'resort' => 38,
     'rc_drone' => 39,
     'wingsuit' => 40,
     'whitewater' => 41,
     'skate_skiing' => 42,
     'yoga' => 43,
     'pilates' => 44,
     'indoor_running' => 45,
     'gravel_cycling' => 46,
     'e_bike_mountain' => 47,
     'commuting' => 48,
     'mixed_surface' => 49,
     'all' => 254,
   },

   'sport_event' => +{
     '_base_type' => FIT_ENUM,
     'uncategorized' => 0,
     'geocaching' => 1,
     'fitness' => 2,
     'recreation' => 3,
     'race' => 4,
     'special_event' => 5,
     'training' => 6,
     'transportation' => 7,
     'touring' => 8,
   },

   'activity' => +{
     '_base_type' => FIT_ENUM,
     'manual' => 0,
     'auto_multi_sport' => 1,
   },

   'intensity' => +{
     '_base_type' => FIT_ENUM,
     'active' => 0,
     'rest' => 1,
     'warmup' => 2,
     'cooldown' => 3,
   },

   'session_trigger' => +{
     '_base_type' => FIT_ENUM,
     'activity_end' => 0,
     'manual' => 1,
     'auto_multi_sport' => 2,
     'fitness_equipment' => 3,
   },

   'autolap_trigger' => +{
     '_base_type' => FIT_ENUM,
     'time' => 0,
     'distance' => 1,
     'position_start' => 2,
     'position_lap' => 3,
     'position_waypoint' => 4,
     'position_marked' => 5,
     'off' => 6,
   },

   'lap_trigger' => +{
     '_base_type' => FIT_ENUM,
     'manual' => 0,
     'time' => 1,
     'distance' => 2,
     'position_start' => 3,
     'position_lap' => 4,
     'position_waypoint' => 5,
     'position_marked' => 6,
     'session_end' => 7,
     'fitness_equipment' => 8,
   },

   'time_mode' => +{
     '_base_type' => FIT_ENUM,
     'hour12' => 0,
     'hour24' => 1,
     'military' => 2,
     'hour_12_with_seconds' => 3,
     'hour_24_with_seconds' => 4,
   },

   'date_mode' => +{
     '_base_type' => FIT_ENUM,
     'day_month' => 0,
     'month_day' => 1,
   },

   'event' => +{
     '_base_type' => FIT_ENUM,
     'timer' => 0,
     'workout' => 3,
     'workout_step' => 4,
     'power_down' => 5,
     'power_up' => 6,
     'off_course' => 7,
     'session' => 8,
     'lap' => 9,
     'course_point' => 10,
     'battery' => 11,
     'virtual_partner_pace' => 12,
     'hr_high_alert' => 13,
     'hr_low_alert' => 14,
     'speed_high_alert' => 15,
     'speed_low_alert' => 16,
     'cad_high_alert' => 17,
     'cad_low_alert' => 18,
     'power_high_alert' => 19,
     'power_low_alert' => 20,
     'recovery_hr' => 21,
     'battery_low' => 22,
     'time_duration_alert' => 23,
     'distance_duration_alert' => 24,
     'calorie_duration_alert' => 25,
     'activity' => 26,
     'fitness_equipment' => 27,
     'length' => 28,
     'user_marker' => 32,
     'sport_point' => 33,
     'calibration' => 36,
     'front_gear_change' => 42,
     'rear_gear_change' => 43,
     'rider_position_change' => 44,
     'elev_high_alert' => 45,
     'elev_low_alert' => 46,
     'comm_timeout' => 47,
   },

   'event_type' => +{
     '_base_type' => FIT_ENUM,
     'start' => 0,
     'stop' => 1,
     'consecutive_depreciated' => 2,
     'marker' => 3,
     'stop_all' => 4,
     'begin_depreciated' => 5,
     'end_depreciated' => 6,
     'end_all_depreciated' => 7,
     'stop_disable' => 8,
     'stop_disable_all' => 9,
   },

   'timer_trigger' => +{
     '_base_type' => FIT_ENUM,
     'manual' => 0,
     'auto' => 1,
     'fitness_equipment' => 2,
   },

   'fitness_equipment_state' => +{
     '_base_type' => FIT_ENUM,
     'ready' => 0,
     'in_use' => 1,
     'paused' => 2,
     'unknown' => 3,
   },

   'autoscroll' => +{
     '_base_type' => FIT_ENUM,
     'none' => 0,
     'slow' => 1,
     'medium' => 2,
     'fast' => 3,
   },

   'activity_class' => +{
     '_base_type' => FIT_ENUM,
     '_mask' => 1,
     'level' => 0x7f,
     'level_max' => 100,
     'athlete' => 0x80,
   },

   'hr_zone_calc' => +{
     '_base_type' => FIT_ENUM,
     'custom' => 0,
     'percent_max_hr' => 1,
     'percent_hrr' => 2,
   },

   'pwr_zone_calc' => +{
     '_base_type' => FIT_ENUM,
     'custom' => 0,
     'percent_ftp' => 1,
   },

   'wkt_step_duration' => +{
     '_base_type' => FIT_ENUM,
     'time' => 0,
     'distance' => 1,
     'hr_less_than' => 2,
     'hr_greater_than' => 3,
     'calories' => 4,
     'open' => 5,
     'repeat_until_steps_cmplt' => 6,
     'repeat_until_time' => 7,
     'repeat_until_distance' => 8,
     'repeat_until_calories' => 9,
     'repeat_until_hr_less_than' => 10,
     'repeat_until_hr_greater_than' => 11,
     'repeat_until_power_less_than' => 12,
     'repeat_until_power_greater_than' => 13,
     'power_less_than' => 14,
     'power_greater_than' => 15,
     'repetition_time' => 28,
   },

   'wkt_step_target' => +{
     '_base_type' => FIT_ENUM,
     'speed' => 0,
     'heart_rate' => 1,
     'open' => 2,
     'cadence' => 3,
     'power' => 4,
     'grade' => 5,
     'resistance' => 6,
   },

   'goal' => +{
     '_base_type' => FIT_ENUM,
     'time' => 0,
     'distance' => 1,
     'calories' => 2,
     'frequency' => 3,
     'steps' => 4,
   },

   'goal_recurrence' => +{
     '_base_type' => FIT_ENUM,
     'off' => 0,
     'daily' => 1,
     'weekly' => 2,
     'monthly' => 3,
     'yearly' => 4,
     'custom' => 5,
   },

   'schedule' => +{
     '_base_type' => FIT_ENUM,
     'workout' => 0,
     'course' => 1,
   },

   'course_point' => +{
     '_base_type' => FIT_ENUM,
     'generic' => 0,
     'summit' => 1,
     'valley' => 2,
     'water' => 3,
     'food' => 4,
     'danger' => 5,
     'left' => 6,
     'right' => 7,
     'straight' => 8,
     'first_aid' => 9,
     'fourth_category' => 10,
     'third_category' => 11,
     'second_category' => 12,
     'first_category' => 13,
     'hors_category' => 14,
     'sprint' => 15,
     'left_fork' => 16,
     'right_fork' => 17,
     'middle_fork' => 18,
     'slight_left' => 19,
     'sharp_left' => 20,
     'slight_right' => 21,
     'sharp_right' => 22,
     'u_turn' => 23,
     'segment_start' => 24,
     'segment_end' => 25,
   },

   'manufacturer' => +{
     '_base_type' => FIT_UINT16,
     'garmin' => 1,
     'garmin_fr405_antfs' => 2,
     'zephyr' => 3,
     'dayton' => 4,
     'idt' => 5,
     'srm' => 6,
     'quarq' => 7,
     'ibike' => 8,
     'saris' => 9,
     'spark_hk' => 10,
     'tanita' => 11,
     'echowell' => 12,
     'dynastream_oem' => 13,
     'nautilus' => 14,
     'dynastream' => 15,
     'timex' => 16,
     'metrigear' => 17,
     'xelic' => 18,
     'beurer' => 19,
     'cardiosport' => 20,
     'a_and_d' => 21,
     'hmm' => 22,
     'suunto' => 23,
     'thita_elektronik' => 24,
     'gpulse' => 25,
     'clean_mobile' => 26,
     'pedal_brain' => 27,
     'peaksware' => 28,
     'saxonar' => 29,
     'lemond_fitness' => 30,
     'dexcom' => 31,
     'wahoo_fitness' => 32,
     'octane_fitness' => 33,
     'archinoetics' => 34,
     'the_hurt_box' => 35,
     'citizen_systems' => 36,
     'magellan' => 37,
     'osynce' => 38,
     'holux' => 39,
     'concept2' => 40,
     'one_giant_leap' => 42,
     'ace_sensor' => 43,
     'brim_brothers' => 44,
     'xplova' => 45,
     'perception_digital' => 46,
     'bf1systems' => 47,
     'pioneer' => 48,
     'spantec' => 49,
     'metalogics' => 50,
     '4iiiis' => 51,
     'seiko_epson' => 52,
     'seiko_epson_oem' => 53,
     'ifor_powell' => 54,
     'maxwell_guider' => 55,
     'star_trac' => 56,
     'breakaway' => 57,
     'alatech_technology_ltd' => 58,
     'mio_technology_europe' => 59,
     'rotor' => 60,
     'geonaute' => 61,
     'id_bike' => 62,
     'specialized' => 63,
     'wtek' => 64,
     'physical_enterprises' => 65,
     'north_pole_engineering' => 66,
     'bkool' => 67,
     'cateye' => 68,
     'stages_cycling' => 69,
     'sigmasport' => 70,
     'tomtom' => 71,
     'peripedal' => 72,
     'wattbike' => 73,
     'moxy' => 76,
     'ciclosport' => 77,
     'powerbahn' => 78,
     'acorn_projects_aps' => 79,
     'lifebeam' => 80,
     'bontrager' => 81,
     'wellgo' => 82,
     'scosche' => 83,
     'magura' => 84,
     'woodway' => 85,
     'elite' => 86,
     'nielsen_kellerman' => 87,
     'dk_city' => 88,
     'tacx' => 89,
     'direction_technology' => 90,
     'magtonic' => 91,
     '1partcarbon' => 92,
     'inside_ride_technologies' => 93,
     'sound_of_motion' => 94,
     'stryd' => 95,
     'icg' => 96,
     'mipulse' => 97,
     'bsx_athletics' => 98,
     'look' => 99,
     'campagnolo_srl' => 100,
     'body_bike_smart' => 101,
     'praxisworks' => 102,
     'limits_technology' => 103,
     'topaction_technology' => 104,
     'cosinuss' => 105,
     'development' => 255,
     'healthandlife' => 257,
     'lezyne' => 258,
     'scribe_labs' => 259,
     'zwift' => 260,
     'watteam' => 261,
     'recon' => 262,
     'favero_electronics' => 263,
     'dynovelo' => 264,
     'strava' => 265,
     'precor' => 266,
     'bryton' => 267,
     'sram' => 268,
     'navman' => 269,
     'cobi' => 270,
     'spivi' => 271,
     'mio_magellan' => 272,
     'evesports' => 273,
     'actigraphcorp' => 5759,
   },

   'garmin_product' => +{
     '_base_type' => FIT_UINT16,
     'hrm_bike' => 0, # not present?
     'hrm1' => 1,
     'axh01' => 2,
     'axb01' => 3,
     'axb02' => 4,
     'hrm2ss' => 5,
     'dsi_alf02' => 6,
     'hrm3ss' => 7,
     'hrm_run_single_byte_product_id' => 8,
     'bsm' => 9,
     'bcm' => 10,
     'axs01' => 11,
     'hrm_tri_single_byte_product_id' => 12,
     'fr225_single_byte_product_id' => 14,
     'fr301_china' => 473,
     'fr301_japan' => 474,
     'fr301_korea' => 475,
     'fr301_taiwan' => 494,
     'fr405' => 717,
     'fr50' => 782,
     'fr405_japan' => 987,
     'fr60' => 988,
     'dsi_alf01' => 1011,
     'fr310xt' => 1018,
     'edge500' => 1036,
     'fr110' => 1124,
     'edge800' => 1169,
     'edge500_taiwan' => 1199,
     'edge500_japan' => 1213,
     'chirp' => 1253,
     'fr110_japan' => 1274,
     'edge200' => 1325,
     'fr910xt' => 1328,
     'edge800_taiwan' => 1333,
     'edge800_japan' => 1334,
     'alf04' => 1341,
     'fr610' => 1345,
     'fr210_japan' => 1360,
     'vector_ss' => 1380,
     'vector_cp' => 1381,
     'edge800_china' => 1386,
     'edge500_china' => 1387,
     'fr610_japan' => 1410,
     'edge500_korea' => 1422,
     'fr70' => 1436,
     'fr310xt_4t' => 1446,
     'amx' => 1461,
     'fr10' => 1482,
     'edge800_korea' => 1497,
     'swim' => 1499,
     'fr910xt_china' => 1537,
     'fenix' => 1551,
     'edge200_taiwan' => 1555,
     'edge510' => 1561,
     'edge810' => 1567,
     'tempe' => 1570,
     'fr910xt_japan' => 1600,
     'fr620' => 1623,
     'fr220' => 1632,
     'fr910xt_korea' => 1664,
     'fr10_japan' => 1688,
     'edge810_japan' => 1721,
     'virb_elite' => 1735,
     'edge_touring' => 1736,
     'edge510_japan' => 1742,
     'hrm_tri' => 1743,
     'hrm_run' => 1752,
     'fr920xt' => 1765,
     'edge510_asia' => 1821,
     'edge810_china' => 1822,
     'edge810_taiwan' => 1823,
     'edge1000' => 1836,
     'vivo_fit' => 1837,
     'virb_remote' => 1853,
     'vivo_ki' => 1885,
     'fr15' => 1903,
     'vivo_active' => 1907,
     'edge510_korea' => 1918,
     'fr620_japan' => 1928,
     'fr620_china' => 1929,
     'fr220_japan' => 1930,
     'fr220_china' => 1931,
     'approach_s6' => 1936,
     'vivo_smart' => 1956,
     'fenix2' => 1967,
     'epix' => 1988,
     'fenix3' => 2050,
     'edge1000_taiwan' => 2052,
     'edge1000_japan' => 2053,
     'fr15_japan' => 2061,
     'edge520' => 2067,
     'edge1000_china' => 2070,
     'fr620_russia' => 2072,
     'fr220_russia' => 2073,
     'vector_s' => 2079,
     'edge1000_korea' => 2100,
     'fr920xt_taiwan' => 2130,
     'fr920xt_china' => 2131,
     'fr920xt_japan' => 2132,
     'virbx' => 2134,
     'vivo_smart_apac' => 2135,
     'etrex_touch' => 2140,
     'edge25' => 2147,
     'fr25' => 2148,
     'vivo_fit2' => 2150,
     'fr225' => 2153,
     'fr630' => 2156,
     'fr230' => 2157,
     'fr735xt' => 2158,
     'vivo_active_apac' => 2160,
     'vector_2' => 2161,
     'vector_2s' => 2162,
     'virbxe' => 2172,
     'fr620_taiwan' => 2173,
     'fr220_taiwan' => 2174,
     'truswing' => 2175,
     'fenix3_china' => 2188,
     'fenix3_twn' => 2189,
     'varia_headlight' => 2192,
     'varia_taillight_old' => 2193,
     'edge_explore_1000' => 2204,
     'fr225_asia' => 2219,
     'varia_radar_taillight' => 2225,
     'varia_radar_display' => 2226,
     'edge20' => 2238,
     'd2_bravo' => 2262,
     'approach_s20' => 2266,
     'varia_remote' => 2276,
     'hrm4_run' => 2327,
     'vivo_active_hr' => 2337,
     'vivo_smart_gps_hr' => 2347,
     'vivo_smart_hr' => 2348,
     'vivo_move' => 2368,
     'varia_vision' => 2398,
     'vivo_fit3' => 2406,
     'fenix3_hr' => 2413,
     'index_smart_scale' => 2429,
     'fr235' => 2431,
     'fenix3_chronos' => 2432,
     'oregon7xx' => 2441,
     'rino7xx' => 2444,
     'nautix' => 2496,
     'edge_820' => 2530,
     'edge_explore_820' => 2531,
     'fenix3_hr' => 2413,
     'fr235' => 2431,
     'fenix5s' => 2544,
     'd2_bravo_titanium' => 2547,
     'running_dynamics_pod' => 2593,
     'fenix5x' => 2604,
     'vivo_fit_jr' => 2606,
     'fr935' => 2691,
     'fenix5' => 2697,
     'sdm4' => 10007,
     'edge_remote' => 10014,
     'training_center' => 20119,
     'connectiq_simulator' => 65531,
     'android_antplus_plugin' => 65532,
     'connect' => 65534,
   },

   'device_type' => +{
     '_moved_to' => 'antplus_device_type',
   },

   'antplus_device_type' => +{
     '_base_type' => FIT_UINT8,
     'antfs' => 1,
     'bike_power' => 11,
     'environment_sensor_legacy' => 12,
     'multi_sport_speed_distance' => 15,
     'control' => 16,
     'fitness_equipment' => 17,
     'blood_pressure' => 18,
     'geocache_node' => 19,
     'light_electric_vehicle' => 20,
     'env_sensor' => 25,
     'racquet' => 26,
     'control_hub' => 27,
     'muscle_oxygen' => 31,
     'bike_light_main' => 35,
     'bike_light_shared' => 36,
     'exd' => 38,
     'bike_radar' => 40,
     'weight_scale' => 119,
     'heart_rate' => 120,
     'bike_speed_cadence' => 121,
     'bike_cadence' => 122,
     'bike_speed' => 123,
     'stride_speed_distance' => 124,
   },

   'ant_network' => +{
     '_base_type' => FIT_ENUM,
     'public' => 0,
     'antplus' => 1,
     'antfs' => 2,
     'private' => 3,
   },

   'workout_capabilities' => +{
     '_base_type' => FIT_UINT32Z,
     '_mask' => 1,
     'interval' => 0x00000001,
     'custom' => 0x00000002,
     'fitness_equipment' => 0x00000004,
     'firstbeat' => 0x00000008,
     'new_leaf' => 0x00000010,
     'tcx' => 0x00000020,
     'speed' => 0x00000080,
     'heart_rate' => 0x00000100,
     'distance' => 0x00000200,
     'cadence' => 0x00000400,
     'power' => 0x00000800,
     'grade' => 0x00001000,
     'resistance' => 0x00002000,
     'protected' => 0x00004000,
   },

   'battery_status' => +{
     '_base_type' => FIT_UINT8,
     'new' => 1,
     'good' => 2,
     'ok' => 3,
     'low' => 4,
     'critical' => 5,
     'charging' => 6,
     'unknown' => 7,
   },

   'hr_type' => +{
     '_base_type' => FIT_ENUM,
     'normal' => 0,
     'irregular' => 1,
   },

   'course_capabilities' => +{
     '_base_type' => FIT_UINT32Z,
     '_mask' => 1,
     'processed' => 0x00000001,
     'valid' => 0x00000002,
     'time' => 0x00000004,
     'distance' => 0x00000008,
     'position' => 0x00000010,
     'heart_rate' => 0x00000020,
     'power' => 0x00000040,
     'cadence' => 0x00000080,
     'training' => 0x00000100,
     'navigation' => 0x00000200,
     'bikeway' => 0x00000400,
   },

   'weight' => +{
     '_base_type' => FIT_UINT16,
     'calculating' => 0xFFFE,
   },

   'workout_hr' => +{
     '_base_type' => FIT_UINT32,
     'bpm_offset' => 100,
   },

   'workout_power' => +{
     '_base_type' => FIT_UINT32,
     'watts_offset' => 1000,
   },

   'bp_status' => +{
     '_base_type' => FIT_ENUM,
     'no_error' => 0,
     'error_incomplete_data' => 1,
     'error_no_measurement' => 2,
     'error_data_out_of_range' => 3,
     'error_irregular_heart_rate' => 4,
   },

   'user_local_id' => +{
     '_base_type' => FIT_UINT16,
     'local_min' => 0x0001,
     'local_max' => 0x000F,
     'stationary_min' => 0x0010,
     'stationary_max' => 0x00FF,
     'portable_min' => 0x0100,
     'portable_max' => 0xFFFE,
   },

   'swim_stroke' => +{
     '_base_type' => FIT_ENUM,
     'freestyle' => 0,
     'backstroke' => 1,
     'breaststroke' => 2,
     'butterfly' => 3,
     'drill' => 4,
     'mixed' => 5,
     'im' => 6,
   },

   'activity_type' => +{
     '_base_type' => FIT_ENUM,
     'generic' => 0,
     'running' => 1,
     'cycling' => 2,
     'transition' => 3,
     'fitness_equipment' => 4,
     'swimming' => 5,
     'walking' => 6,
     'all' => 254,
   },

   'activity_subtype' => +{
     '_base_type' => FIT_ENUM,
     'generic' => 0,
     'treadmill' => 1,
     'street' => 2,
     'trail' => 3,
     'track' => 4,
     'spin' => 5,
     'indoor_cycling' => 6,
     'road' => 7,
     'mountain' => 8,
     'downhill' => 9,
     'recumbent' => 10,
     'cyclocross' => 11,
     'hand_cycling' => 12,
     'track_cycling' => 13,
     'indoor_rowing' => 14,
     'elliptical' => 15,
     'stair_climbing' => 16,
     'lap_swimming' => 17,
     'open_water' => 18,
     'all' => 254,
   },

   'activity_level' => +{
     '_base_type' => FIT_ENUM,
     'low' => 0,
     'medium' => 1,
     'high' => 2,
   },

   'side' => +{
     '_base_type' => FIT_ENUM,
     'right' => 0,
     'left' => 1,
   },

   'left_right_balance' => +{
     '_base_type' => FIT_UINT8,
     'mask' => 0x7F,
     'right' => 0x80,
   },

   'left_right_balance_100' => +{
     '_base_type' => FIT_UINT16,
     'mask' => 0x3FFF,
     'right' => 0x8000,
   },

   'length_type' => +{
     '_base_type' => FIT_ENUM,
     'idle' => 0,
     'active' => 1,
   },

   'day_of_week' => +{
     '_base_type' => FIT_ENUM,
     'sunday' => 0,
     'monday' => 1,
     'tuesday' => 2,
     'wednesday' => 3,
     'thursday' => 4,
     'friday' => 5,
     'saturday' => 6,
   },

   'connectivity_capabilities' => +{
     '_base_type' => FIT_UINT32Z,
     'bluetooth' => 0x00000001,
     'bluetooth_le' => 0x00000002,
     'ant' => 0x00000004,
     'activity_upload' => 0x00000008,
     'course_download' => 0x00000010,
     'workout_download' => 0x00000020,
     'live_track' => 0x00000040,
     'weather_conditions' => 0x00000080,
     'weather_alerts' => 0x00000100,
     'gps_ephemeris_download' => 0x00000200,
     'explicit_archive' => 0x00000400,
     'setup_incomplete' => 0x00000800,
     'continue_sync_after_software_update' => 0x00001000,
     'connect_iq_app_download' => 0x00002000,
     'golf_course_download' => 0x00004000,
     'device_initiates_sync' => 0x00008000,
     'connect_iq_watch_app_download' => 0x00010000,
     'connect_iq_widget_download' => 0x00020000,
     'connect_iq_watch_face_download' => 0x00040000,
     'connect_iq_data_field_download' => 0x00080000,
     'connect_iq_app_managment' => 0x00100000,
     'swing_sensor' => 0x00200000,
     'swing_sensor_remote' => 0x00400000,
     'incident_detection' => 0x00800000,
     'audio_prompts' => 0x01000000,
     'wifi_verification' => 0x02000000,
     'true_up' => 0x04000000,
     'find_my_watch' => 0x08000000,
     'remote_manual_sync' => 0x10000000,
     'live_track_auto_start' => 0x20000000,
   },

   'weather_report' => +{
     '_base_type' => FIT_ENUM,
     'current' => 0,
#    'forecast' => 1, # deprecated, use hourly_forecast instead
     'hourly_forecast' => 1,
     'daily_forecast' => 2,
   },

   'weather_status' => +{
     '_base_type' => FIT_ENUM,
     'clear' => 0,
     'partly_cloudy' => 1,
     'mostly_cloudy' => 2,
     'rain' => 3,
     'snow' => 4,
     'windy' => 5,
     'thunderstorms' => 6,
     'wintry_mix' => 7,
     'fog' => 8,
     'hazy' => 11,
     'hail' => 12,
     'scattered_showers' => 13,
     'scattered_thunderstorms' => 14,
     'unknown_precipitation' => 15,
     'light_rain' => 16,
     'heavy_rain' => 17,
     'light_snow' => 18,
     'heavy_snow' => 19,
     'light_rain_snow' => 20,
     'heavy_rain_snow' => 21,
     'cloudy' => 22,
   },

   'weather_severity' => +{
     '_base_type' => FIT_ENUM,
     'unknown' => 0,
     'warning' => 1,
     'watch' => 2,
     'advisory' => 3,
     'statement' => 4,
   },

   'weather_severe_type' => +{
     '_base_type' => FIT_ENUM,
     'unspecified' => 0,
     'tornado' => 1,
     'tsunami' => 2,
     'hurricane' => 3,
     'extreme_wind' => 4,
     'typhoon' => 5,
     'inland_hurricane' => 6,
     'hurricane_force_wind' => 7,
     'waterspout' => 8,
     'severe_thunderstorm' => 9,
     'wreckhouse_winds' => 10,
     'les_suetes_wind' => 11,
     'avalanche' => 12,
     'flash_flood' => 13,
     'tropical_storm' => 14,
     'inland_tropical_storm' => 15,
     'blizzard' => 16,
     'ice_storm' => 17,
     'freezing_rain' => 18,
     'debris_flow' => 19,
     'flash_freeze' => 20,
     'dust_storm' => 21,
     'high_wind' => 22,
     'winter_storm' => 23,
     'heavy_freezing_spray' => 24,
     'extreme_cold' => 25,
     'wind_chill' => 26,
     'cold_wave' => 27,
     'heavy_snow_alert' => 28,
     'lake_effect_blowing_snow' => 29,
     'snow_squall' => 30,
     'lake_effect_snow' => 31,
     'winter_weather' => 32,
     'sleet' => 33,
     'snowfall' => 34,
     'snow_and_blowing_snow' => 35,
     'blowing_snow' => 36,
     'snow_alert' => 37,
     'arctic_outflow' => 38,
     'freezing_drizzle' => 39,
     'storm' => 40,
     'storm_surge' => 41,
     'rainfall' => 42,
     'areal_flood' => 43,
     'coastal_flood' => 44,
     'lakeshore_flood' => 45,
     'excessive_heat' => 46,
     'heat' => 47,
     'weather' => 48,
     'high_heat_and_humidity' => 49,
     'humidex_and_health' => 50,
     'humidex' => 51,
     'gale' => 52,
     'freezing_spray' => 53,
     'special_marine' => 54,
     'squall' => 55,
     'strong_wind' => 56,
     'lake_wind' => 57,
     'marine_weather' => 58,
     'wind' => 59,
     'small_craft_hazardous_seas' => 60,
     'hazardous_seas' => 61,
     'small_craft' => 62,
     'small_craft_winds' => 63,
     'small_craft_rough_bar' => 64,
     'high_water_level' => 65,
     'ashfall' => 66,
     'freezing_fog' => 67,
     'dense_fog' => 68,
     'dense_smoke' => 69,
     'blowing_dust' => 70,
     'hard_freeze' => 71,
     'freeze' => 72,
     'frost' => 73,
     'fire_weather' => 74,
     'flood' => 75,
     'rip_tide' => 76,
     'high_surf' => 77,
     'smog' => 78,
     'air_quality' => 79,
     'brisk_wind' => 80,
     'air_stagnation' => 81,
     'low_water' => 82,
     'hydrological' => 83,
     'special_weather' => 84,
   },

   'stroke_type' => +{
     '_base_type' => FIT_ENUM,
     'no_event' => 0,
     'other' => 1,
     'serve' => 2,
     'forehand' => 3,
     'backhand' => 4,
     'smash' => 5,
   },

   'body_location' => +{
     '_base_type' => FIT_ENUM,
     'left_leg' => 0,
     'left_calf' => 1,
     'left_shin' => 2,
     'left_hamstring' => 3,
     'left_quad' => 4,
     'left_glute' => 5,
     'right_leg' => 6,
     'right_calf' => 7,
     'right_shin' => 8,
     'right_hamstring' => 9,
     'right_quad' => 10,
     'right_glute' => 11,
     'torso_back' => 12,
     'left_lower_back' => 13,
     'left_upper_back' => 14,
     'right_lower_back' => 15,
     'right_upper_back' => 16,
     'torso_front' => 17,
     'left_abdomen' => 18,
     'left_chest' => 19,
     'right_abdomen' => 20,
     'right_chest' => 21,
     'left_arm' => 22,
     'left_shoulder' => 23,
     'left_bicep' => 24,
     'left_tricep' => 25,
     'left_brachioradialis' => 26,
     'left_forearm_extensors' => 27,
     'right_arm' => 28,
     'right_shoulder' => 29,
     'right_bicep' => 30,
     'right_tricep' => 31,
     'right_brachioradialis' => 32,
     'right_forearm_extensors' => 33,
     'neck' => 34,
     'throat' => 35,
     'waist_mid_back' => 36,
     'waist_front' => 37,
     'waist_left' => 38,
     'waist_right' => 39,
   },

   'segment_lap_status' => +{
     '_base_type' => FIT_ENUM,
     'end' => 0,
     'fail' => 1,
   },

   'segment_leaderboard_type' => +{
     '_base_type' => FIT_ENUM,
     'overall' => 0,
     'personal_best' => 1,
     'connections' => 2,
     'group' => 3,
     'challenger' => 4,
     'kom' => 5,
     'qom' => 6,
     'pr' => 7,
     'goal' => 8,
     'rival' => 9,
     'club_leader' => 10,
   },

   'segment_delete_status' => +{
     '_base_type' => FIT_ENUM,
     'do_not_delete' => 0,
     'delete_one' => 1,
     'delete_all' => 2,
   },

   'segment_selection_type' => +{
     '_base_type' => FIT_ENUM,
     'starred' => 0,
     'suggested' => 1,
   },

   'source_type' => +{
     '_base_type' => FIT_ENUM,
     'ant' => 0,
     'antplus' => 1,
     'bluetooth' => 2,
     'bluetooth_low_energy' => 3,
     'wifi' => 4,
     'local' => 5,
   },

   'display_orientation' => +{
     '_base_type' => FIT_ENUM,
     'auto' => 0,
     'portrait' => 1,
     'landscape' => 2,
     'portrait_flipped' => 3,
     'landscape_flipped' => 4,
   },

   'rider_position_type' => +{
     '_base_type' => FIT_ENUM,
     'seated' => 0,
     'standing' => 1,
   },

   'power_phase_type' => +{
     '_base_type' => FIT_ENUM,
     'power_phase_start_angle' => 0,
     'power_phase_end_angle' => 1,
     'power_phase_arc_length' => 2,
     'power_phase_center' => 3,
   },

   'camera_event_type' => +{
     '_base_type' => FIT_ENUM,
     'video_start' => 0,
     'video_split' => 1,
     'video_end' => 2,
     'photo_taken' => 3,
     'video_second_stream_start' => 4,
     'video_second_stream_split' => 5,
     'video_second_stream_end' => 6,
     'video_split_start' => 7,
     'video_second_stream_split_start' => 8,
     'video_pause' => 11,
     'video_second_stream_pause' => 12,
     'video_resume' => 13,
     'video_second_stream_resume' => 14,
   },

   'sensor_type' => +{
     '_base_type' => FIT_ENUM,
     'accelerometer' => 0,
     'gyroscope' => 1,
     'compass' => 2,
   },

   'bike_light_network_config_type' => +{
     '_base_type' => FIT_ENUM,
     'auto' => 0,
     'individual' => 4,
     'high_visibility' => 5,
     'trail' => 6,
   },

   'comm_timeout_type' => +{
     '_base_type' => FIT_UINT16,
     'wildcard_pairing_timeout' => 0,
     'pairing_timeout' => 1,
     'connection_lost' => 2,
     'connection_timeout' => 3,
   },

   'camera_orientation_type' => +{
     '_base_type' => FIT_ENUM,
     'camera_orientation_0' => 0,
     'camera_orientation_90' => 1,
     'camera_orientation_180' => 2,
     'camera_orientation_270' => 3,
   },

   'attitude_stage' => +{
     '_base_type' => FIT_ENUM,
     'failed' => 0,
     'aligning' => 1,
     'degraded' => 2,
     'valid' => 3,
   },

   'attitude_validity' => +{
     '_base_type' => FIT_UINT16,
     'track_angle_heading_valid' => 0x0001,
     'pitch_valid' => 0x0002,
     'roll_valid' => 0x0004,
     'lateral_body_accel_valid' => 0x0008,
     'normal_body_accel_valid' => 0x0010,
     'turn_rate_valid' => 0x0020,
     'hw_fail' => 0x0040,
     'mag_invalid' => 0x0080,
     'no_gps' => 0x0100,
     'gps_invalid' => 0x0200,
     'solution_coasting' => 0x0400,
     'true_track_angle' => 0x0800,
     'magnetic_heading' => 0x1000,
   },

   'auto_sync_frequency' => +{
     '_base_type' => FIT_ENUM,
     'never' => 0,
     'occasionally' => 1,
     'frequent' => 2,
     'once_a_day' => 3,
   },

   'exd_layout' => +{
     '_base_type' => FIT_ENUM,
     'full_screen' => 0,
     'half_vertical' => 1,
     'half_horizontal' => 2,
     'half_vertical_right_split' => 3,
     'half_horizontal_bottom_split' => 4,
     'full_quarter_split' => 5,
     'half_vertical_left_split' => 6,
     'half_horizontal_top_split' => 7,
   },

   'exd_display_type' => +{
     '_base_type' => FIT_ENUM,
     'numerical' => 0,
     'simple' => 1,
     'graph' => 2,
     'bar' => 3,
     'circle_graph' => 4,
     'virtual_partner' => 5,
     'balance' => 6,
     'string_list' => 7,
     'string' => 8,
     'simple_dynamic_icon' => 9,
     'gauge' => 10,
   },

   'exd_data_units' => +{
     '_base_type' => FIT_ENUM,
     'no_units' => 0,
     'laps' => 1,
     'miles_per_hour' => 2,
     'kilometers_per_hour' => 3,
     'feet_per_hour' => 4,
     'meters_per_hour' => 5,
     'degrees_celsius' => 6,
     'degrees_farenheit' => 7,
     'zone' => 8,
     'gear' => 9,
     'rpm' => 10,
     'bpm' => 11,
     'degrees' => 12,
     'millimeters' => 13,
     'meters' => 14,
     'kilometers' => 15,
     'feet' => 16,
     'yards' => 17,
     'kilofeet' => 18,
     'miles' => 19,
     'time' => 20,
     'enum_turn_type' => 21,
     'percent' => 22,
     'watts' => 23,
     'watts_per_kilogram' => 24,
     'enum_battery_status' => 25,
     'enum_bike_light_beam_angle_mode' => 26,
     'enum_bike_light_battery_status' => 27,
     'enum_bike_light_network_config_type' => 28,
     'lights' => 29,
     'seconds' => 30,
     'minutes' => 31,
     'hours' => 32,
     'calories' => 33,
     'kilojoules' => 34,
     'milliseconds' => 35,
     'second_per_mile' => 36,
     'second_per_kilometer' => 37,
     'centimeter' => 38,
     'enum_course_point' => 39,
     'bradians' => 40,
     'enum_sport' => 41,
   },

   'exd_qualifiers' => +{
     '_base_type' => FIT_ENUM,
     'no_qualifier' => 0,
     'instantaneous' => 1,
     'average' => 2,
     'lap' => 3,
     'maximum' => 4,
     'maximum_average' => 5,
     'maximum_lap' => 6,
     'last_lap' => 7,
     'average_lap' => 8,
     'to_destination' => 9,
     'to_go' => 10,
     'to_next' => 11,
     'next_course_point' => 12,
     'total' => 13,
     'three_second_average' => 14,
     'ten_second_average' => 15,
     'thirty_second_average' => 16,
     'percent_maximum' => 17,
     'percent_maximum_average' => 18,
     'lap_percent_maximum' => 19,
     'elapsed' => 20,
     'sunrise' => 21,
     'sunset' => 22,
     'compared_to_virtual_partner' => 23,
     'maximum_24h' => 24,
     'minimum_24h' => 25,
     'minimum' => 26,
     'first' => 27,
     'second' => 28,
     'third' => 29,
     'shifter' => 30,
     'last_sport' => 31,
     'zone_9' => 242,
     'zone_8' => 243,
     'zone_7' => 244,
     'zone_6' => 245,
     'zone_5' => 246,
     'zone_4' => 247,
     'zone_3' => 248,
     'zone_2' => 249,
     'zone_1' => 250,
   },

   'exd_descriptors' => +{
     '_base_type' => FIT_ENUM,
     'bike_light_battery_status' => 0,
     'beam_angle_status' => 1,
     'batery_level' => 2,
     'light_network_mode' => 3,
     'number_lights_connected' => 4,
     'cadence' => 5,
     'distance' => 6,
     'estimated_time_of_arrival' => 7,
     'heading' => 8,
     'time' => 9,
     'battery_level' => 10,
     'trainer_resistance' => 11,
     'trainer_target_power' => 12,
     'time_seated' => 13,
     'time_standing' => 14,
     'elevation' => 15,
     'grade' => 16,
     'ascent' => 17,
     'descent' => 18,
     'vertical_speed' => 19,
     'di2_battery_level' => 20,
     'front_gear' => 21,
     'rear_gear' => 22,
     'gear_ratio' => 23,
     'heart_rate' => 24,
     'heart_rate_zone' => 25,
     'time_in_heart_rate_zone' => 26,
     'heart_rate_reserve' => 27,
     'calories' => 28,
     'gps_accuracy' => 29,
     'gps_signal_strength' => 30,
     'temperature' => 31,
     'time_of_day' => 32,
     'balance' => 33,
     'pedal_smoothness' => 34,
     'power' => 35,
     'functional_threshold_power' => 36,
     'intensity_factor' => 37,
     'work' => 38,
     'power_ratio' => 39,
     'normalized_power' => 40,
     'training_stress_score' => 41,
     'time_on_zone' => 42,
     'speed' => 43,
     'laps' => 44,
     'reps' => 45,
     'workout_step' => 46,
     'course_distance' => 47,
     'navigation_distance' => 48,
     'course_estimated_time_of_arrival' => 49,
     'navigation_estimated_time_of_arrival' => 50,
     'course_time' => 51,
     'navigation_time' => 52,
     'course_heading' => 53,
     'navigation_heading' => 54,
     'power_zone' => 55,
     'torque_effectiveness' => 56,
     'timer_time' => 57,
     'power_weight_ratio' => 58,
     'left_platform_center_offset' => 59,
     'right_platform_center_offset' => 60,
     'left_power_phase_start_angle' => 61,
     'right_power_phase_start_angle' => 62,
     'left_power_phase_finish_angle' => 63,
     'right_power_phase_finish_angle' => 64,
     'gears' => 65,
     'pace' => 66,
     'training_effect' => 67,
     'vertical_oscillation' => 68,
     'vertical_ratio' => 69,
     'ground_contact_time' => 70,
     'left_ground_contact_time_balance' => 71,
     'right_ground_contact_time_balance' => 72,
     'stride_length' => 73,
     'running_cadence' => 74,
     'performance_condition' => 75,
     'course_type' => 76,
     'time_in_power_zone' => 77,
     'navigation_turn' => 78,
     'course_location' => 79,
     'navigation_location' => 80,
     'compass' => 81,
     'gear_combo' => 82,
     'muscle_oxygen' => 83,
     'icon' => 84,
     'compass_heading' => 85,
     'gps_heading' => 85,
     'gps_elevation' => 86,
     'anaerobic_training_effect' => 87,
     'course' => 89,
     'off_course' => 90,
     'glide_ratio' => 91,
     'vertical_distance' => 92,
     'vmg' => 93,
     'ambient_pressure' => 94,
     'pressure' => 95
   },

   'auto_activity_detect' => +{
     '_base_type' => FIT_UINT32,
     'none' => 0x00000000,
     'running' => 0x00000001,
     'cycling' => 0x00000002,
     'swimming' => 0x00000004,
     'walking' => 0x00000008,
     'elliptical' => 0x00000020,
     'sedentary' => 0x00000400,
   },

   'supported_exd_screen_layouts' => +{
     '_base_type' => FIT_UINT32Z,
     'full_screen' => 0x00000001,
     'half_vertical' => 0x00000002,
     'half_horizontal' => 0x00000004,
     'half_vertical_right_split' => 0x00000008,
     'half_horizontal_bottom_split' => 0x00000010,
     'full_quarter_split' => 0x00000020,
     'half_vertical_left_split' => 0x00000040,
     'half_horizontal_top_split' => 0x00000080,
   },

   'fit_base_type' => +{
     '_base_type' => FIT_UINT8,
     'enum' => 0,
     'sint8' => 1,
     'uint8' => 2,
     'sint16' => 131,
     'uint16' => 132,
     'sint32' => 133,
     'uint32' => 134,
     'string' => 7,
     'float32' => 136,
     'float64' => 137,
     'uint8z' => 10,
     'uint16z' => 139,
     'uint32z' => 140,
     'byte' => 13,
     'sint64' => 142,
     'uint64' => 143,
     'uint64z' => 144,
   },

   'turn_type' => +{
     '_base_type' => FIT_ENUM,
     'arriving_idx' => 0,
     'arriving_left_idx' => 1,
     'arriving_right_idx' => 2,
     'arriving_via_idx' => 3,
     'arriving_via_left_idx' => 4,
     'arriving_via_right_idx' => 5,
     'bear_keep_left_idx' => 6,
     'bear_keep_right_idx' => 7,
     'continue_idx' => 8,
     'exit_left_idx' => 9,
     'exit_right_idx' => 10,
     'ferry_idx' => 11,
     'roundabout_45_idx' => 12,
     'roundabout_90_idx' => 13,
     'roundabout_135_idx' => 14,
     'roundabout_180_idx' => 15,
     'roundabout_225_idx' => 16,
     'roundabout_270_idx' => 17,
     'roundabout_315_idx' => 18,
     'roundabout_360_idx' => 19,
     'roundabout_neg_45_idx' => 20,
     'roundabout_neg_90_idx' => 21,
     'roundabout_neg_135_idx' => 22,
     'roundabout_neg_180_idx' => 23,
     'roundabout_neg_225_idx' => 24,
     'roundabout_neg_270_idx' => 25,
     'roundabout_neg_315_idx' => 26,
     'roundabout_neg_360_idx' => 27,
     'roundabout_generic_idx' => 28,
     'roundabout_neg_generic_idx' => 29,
     'sharp_turn_left_idx' => 30,
     'sharp_turn_right_idx' => 31,
     'turn_left_idx' => 32,
     'turn_right_idx' => 33,
     'uturn_left_idx' => 34,
     'uturn_right_idx' => 35,
     'icon_inv_idx' => 36,
     'icon_idx_cnt' => 37,
   },

   'bike_light_beam_angle_mode' => +{
     '_base_type' => FIT_ENUM,
     'manual' => 0,
     'auto' => 1,
   },

   'fit_base_unit' => +{
     '_base_type' => FIT_UINT16,
     'other' => 0,
   },

   'switch' => +{ # not present?
     '_base_type' => FIT_ENUM,
     'off' => 0,
     'on' => 1,
     'auto' => 2,
   },

   );

my ($typenam, $typdesc);

foreach $typename (keys %named_type) {
  $typedesc = $named_type{$typenam};

  if ($typedesc->{_moved_to} ne '') {
    my $to = $named_type{$typedesc->{_moved_to}};

    ref $to eq 'HASH' and $named_type{$typenam} = +{%$to};
  }
}

while ((undef, $typedesc) = each %named_type) {
  my $name;

  foreach $name (grep {!/^_/} keys %$typedesc) {
    $typedesc->{$typedesc->{$name}} = $name;
  }
}

$use_gmtime = 0;

sub use_gmtime {
  my $self = shift;

  if (@_) {
    if (ref $self eq '') {
      $use_gmtime = $_[0];
    }
    else {
      $self->{use_gmtime} = $_[0];
    }
  }
  elsif (ref $self eq '') {
    $use_gmtime;
  }
  else {
    $self->{use_gmtime};
  }
}

sub numeric_date_time {
  my $self = shift;

  if (@_) {
    $self->{numeric_date_time} = $_[0];
  }
  else {
    $self->{numeric_date_time};
  }
}

sub date_string {
  my ($self, $time) = @_;
  my ($s, $mi, $h, $d, $mo, $y, $gmt) = $self->use_gmtime ? ((gmtime($time))[0 .. 5], 'Z') : (localtime($time))[0 .. 5];

  sprintf('%04u-%02u-%02uT%02u:%02u:%02u%s', $y + 1900, $mo + 1, $d, $h, $mi, $s, $gmt);
}

sub named_type_value {
  my ($self, $type_name, $val) = @_;
  my $typedesc = $named_type{$type_name};

  if (ref $typedesc ne 'HASH') {
    $self->error("$type_name is not a named type");
  }
  elsif ($typedesc->{_mask}) {
    if ($val !~ /^[-+]?\d+$/) {
      my $num = 0;
      my $expr;

      foreach $expr (split /,/, $val) {
	$expr =~ s/^.*=//;

	if ($expr =~ s/^0[xX]//) {
	  $num |= hex($expr);
	}
	else {
	  $num |= $expr + 0;
	}
      }

      $num;
    }
    else {
      my $mask = 0;
      my (@key, $key);

      foreach $key (sort {$typedesc->{$b} <=> $typedesc->{$a}} grep {/^[A-Za-z]/} keys %$typedesc) {
	push @key, $key . '=' . ($val & $typedesc->{$key});
	$mask |= $typedesc->{$key};
      }

      my $rest = $val & ~$mask & ((1 << ($size[$typedesc->{_base_type}] * 8)) - 1);

      if ($rest) {
	my $width = $size[$typedesc->{_base_type}] * 2;

	join(',', @key, sprintf("0x%0${width}X", $rest));
      }
      elsif (@key) {
	join(',', @key);
      }
      else {
	0;
      }
    }
  }
  elsif ($type_name eq 'date_time') {
    if ($val !~ /^[-+]?\d+$/) {
      my ($y, $mo, $d, $h, $mi, $s, $gmt) = $val =~ /(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)([zZ]?)/;

      ($gmt ne '' ? timegm($s, $mi, $h, $d, $mo - 1, $y - 1900) : timelocal($s, $mi, $h, $d, $mo - 1, $y - 1900)) + $typedesc->{_offset};
    }
    elsif ($val >= $typedesc->{_min} && $val != $invalid[$typedesc->{_base_type}]) {
      if ($self->numeric_date_time) {
	$val - $typedesc->{_offset};
      }
      else {
	$self->date_string($val - $typedesc->{_offset});
      }
    }
    else {
      undef;
    }
  }
  else {
    $typedesc->{$val};
  }
}

%msgtype_by_name =
  (

   # =================== Common messages ===================
   'file_id' => +{
     0 => +{'name' => 'type', 'type_name' => 'file'},
     1 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     2 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream_oem' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     3 => +{'name' => 'serial_number'},
     4 => +{'name' => 'time_created', 'type_name' => 'date_time'},
     5 => +{'name' => 'number'},
     7 => +{'name' => 'unknown7'}, # unknown UINT32
     8 => +{'name' => 'product_name'},
   },

   'file_creator' => +{
     0 => +{'name' => 'software_version'},
     1 => +{'name' => 'hardware_version'},
   },

   'timestamp_correlation' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'fractional_timestamp', 'scale' => 32768, 'unit' => 's'},
     1 => +{'name' => 'system_timestamp', 'type_name' => 'date_time'},
     2 => +{'name' => 'fractional_system_timestamp', 'scale' => 32768, 'unit' => 's'},
     3 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
     4 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     5 => +{'name' => 'system_timestamp_ms', 'unit' => 'ms'},
   },

   # =================== ??? ===================
   'pad' => +{
     0 => +{'name' => 'pad'},
   },

   # =================== Device file messages ===================
   'software' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     3 => +{'name' => 'version', 'scale' => 100},
     5 => +{'name' => 'part_number'},
   },

   'slave_device' => +{
     0 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     1 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream_oem' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },
   },

   'capabilities' => +{
     0 => +{'name' => 'languages'},
     1 => +{'name' => 'sports', 'type_name' => 'sport_bits_0'},
     21 => +{'name' => 'workouts_supported', 'type_name' => 'workout_capabilities'},
     22 => +{'name' => 'unknown22'}, # unknown ENUM
     23 => +{'name' => 'connectivity_supported', 'type_name' => 'connectivity_capabilities'},
     24 => +{'name' => 'unknown24'}, # unknown ENUM
     25 => +{'name' => 'unknown25'}, # unknown UINT32Z
   },

   'file_capabilities' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'type', 'type_name' => 'file'},
     1 => +{'name' => 'flags', 'type_name' => 'file_flags'},
     2 => +{'name' => 'directory'},
     3 => +{'name' => 'max_count'},
     4 => +{'name' => 'max_size', 'unit' => 'bytes'},
   },

   'mesg_capabilities' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'file', 'type_name' => 'file'},
     1 => +{'name' => 'mesg_num', 'type_name' => 'mesg_num'},
     2 => +{'name' => 'count_type', 'type_name' => 'mesg_count'},

     3 => +{
       'name' => 'count',

       'switch' => +{
	 '_by' => 'count_type',
	 'num_per_file' => +{'name' => 'num_per_file'},
	 'max_per_file' => +{'name' => 'max_per_file'},
	 'max_per_file_type' => +{'name' => 'max_per_file_type'},
       },
     },
   },

   'field_capabilities' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'file', 'type_name' => 'file'},
     1 => +{'name' => 'mesg_num', 'type_name' => 'mesg_num'},
     2 => +{'name' => 'field_num'},
     3 => +{'name' => 'count'},
     4 => +{'name' => 'bits'}, # not present?
   },

   # =================== Settings file messages ===================
   'device_settings' => +{
     0 => +{'name' => 'active_time_zone'},
     1 => +{'name' => 'utc_offset'},
     2 => +{'name' => 'time_offset', 'unit' => 's'},
     3 => +{'name' => 'unknown3'}, # unknown ENUM
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'time_zone_offset', 'scale' => 4, 'unit' => 'hr'},
     10 => +{'name' => 'unknown10'}, # unknown ENUM
     11 => +{'name' => 'unknown11'}, # unknown ENUM
     12 => +{'name' => 'unknown12'}, # unknown ENUM
     13 => +{'name' => 'unknown13'}, # unknown UINT8
     14 => +{'name' => 'unknown14'}, # unknown UINT8
     15 => +{'name' => 'unknown15'}, # unknown UINT8
     16 => +{'name' => 'unknown16'}, # unknown ENUM
     17 => +{'name' => 'unknown17'}, # unknown ENUM
     18 => +{'name' => 'unknown18'}, # unknown ENUM
     21 => +{'name' => 'unknown21'}, # unknown ENUM
     22 => +{'name' => 'unknown22'}, # unknown ENUM
     26 => +{'name' => 'unknown26'}, # unknown ENUM
     27 => +{'name' => 'unknown27'}, # unknown ENUM
     29 => +{'name' => 'unknown29'}, # unknown ENUM
     36 => +{'name' => 'activity_tracker_enabled', 'type_name' => 'bool'},
     38 => +{'name' => 'unknown38'}, # unknown ENUM
     40 => +{'name' => 'pages_enabled'},
     41 => +{'name' => 'unknown41'}, # unknown ENUM
     46 => +{'name' => 'move_alert_enabled', 'type_name' => 'bool'},
     47 => +{'name' => 'date_mode', 'type_name' => 'date_mode'},
     48 => +{'name' => 'unknown48'}, # unknown ENUM
     49 => +{'name' => 'unknown49'}, # unknown UINT16
     52 => +{'name' => 'unknown52'}, # unknown ENUM
     53 => +{'name' => 'unknown53'}, # unknown ENUM
     54 => +{'name' => 'unknown54'}, # unknown ENUM
     55 => +{'name' => 'display_orientation', 'type_name' => 'display_orientation'},
     56 => +{'name' => 'mounting_side', 'type_name' => 'side'},
     57 => +{'name' => 'default_page'},
     58 => +{'name' => 'autosync_min_steps', 'unit' => 'steps'},
     59 => +{'name' => 'autosync_min_time', 'unit' => 'minutes'},
     85 => +{'name' => 'unknown85'}, # unknown ENUM
     89 => +{'name' => 'auto_sync_frequency', 'type_name' => 'auto_sync_frequency'},
     94 => +{'name' => 'number_of_screens'},
     95 => +{'name' => 'smart_notification_display_orientation', 'type_name' => 'display_orientation'},
   },

   'user_profile' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'friendly_name'},
     1 => +{'name' => 'gender', 'type_name' => 'gender'},
     2 => +{'name' => 'age', 'unit' => 'years'},
     3 => +{'name' => 'height', scale => 100, 'unit' => 'm'},
     4 => +{'name' => 'weight', scale => 10, 'unit' => 'kg'},
     5 => +{'name' => 'language', 'type_name' => 'language'},
     6 => +{'name' => 'elev_setting', 'type_name' => 'display_measure'},
     7 => +{'name' => 'weight_setting', 'type_name' => 'display_measure'},
     8 => +{'name' => 'resting_heart_rate', 'unit' => 'bpm'},
     9 => +{'name' => 'default_max_running_heart_rate', 'unit' => 'bpm'},
     10 => +{'name' => 'default_max_biking_heart_rate', 'unit' => 'bpm'},
     11 => +{'name' => 'default_max_heart_rate', 'unit' => 'bpm'},
     12 => +{'name' => 'hr_setting', 'type_name' => 'display_heart'},
     13 => +{'name' => 'speed_setting', 'type_name' => 'display_measure'},
     14 => +{'name' => 'dist_setting', 'type_name' => 'display_measure'},
     16 => +{'name' => 'power_setting', 'type_name' => 'display_power'},
     17 => +{'name' => 'activity_class', 'type_name' => 'activity_class'},
     18 => +{'name' => 'position_setting', 'type_name' => 'display_position'},
     21 => +{'name' => 'temperature_setting', 'type_name' => 'display_measure'},
     22 => +{'name' => 'local_id', 'type_name' => 'user_local_id'},
     23 => +{'name' => 'global_id'},
     24 => +{'name' => 'unknown24'}, # unknown UINT8
     28 => +{'name' => 'unknown28'}, # unknown UINT32
     29 => +{'name' => 'unknown29'}, # unknown UINT32
     30 => +{'name' => 'height_setting', 'type_name' => 'display_measure'},
     31 => +{'name' => 'unknown31'}, # unknown UINT16
     32 => +{'name' => 'unknown32'}, # unknown UINT16
     33 => +{'name' => 'unknown33'}, # unknown UINT16
     34 => +{'name' => 'unknown34'}, # unknown UINT16
     35 => +{'name' => 'unknown35'}, # unknown UINT32
     36 => +{'name' => 'unknown36'}, # unknown UINT8
     38 => +{'name' => 'unknown38'}, # unknown UINT16
     40 => +{'name' => 'unknown40'}, # unknown FLOAT32
     42 => +{'name' => 'unknown42'}, # unknown UINT32
   },

   'hrm_profile' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'enabled'},
     1 => +{'name' => 'hrm_ant_id'},
     2 => +{'name' => 'log_hrv'},
     3 => +{'name' => 'hrm_ant_id_trans_type'},
   },

   'sdm_profile' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'enabled'},
     1 => +{'name' => 'sdm_ant_id'},
     2 => +{'name' => 'sdm_cal_factor', 'scale' => 10, 'unit' => '%'},
     3 => +{'name' => 'odometer', 'scale' => 100, 'unit' => 'm'},
     4 => +{'name' => 'speed_source'},
     5 => +{'name' => 'sdm_ant_id_trans_type'},
     7 => +{'name' => 'odometer_rollover'},
   },

   'bike_profile' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'name'},
     1 => +{'name' => 'sport', 'type_name' => 'sport'},
     2 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     3 => +{'name' => 'odometer', 'scale' => 100, 'unit' => 'm'},
     4 => +{'name' => 'bike_spd_ant_id'},
     5 => +{'name' => 'bike_cad_ant_id'},
     6 => +{'name' => 'bike_spdcad_ant_id'},
     7 => +{'name' => 'bike_power_ant_id'},
     8 => +{'name' => 'custom_wheelsize', 'scale' => 1000, 'unit' => 'm'},
     9 => +{'name' => 'auto_wheelsize', 'scale' => 1000, 'unit' => 'm'},
     10 => +{'name' => 'bike_weight', 'scale' => 10, 'unit' => 'kg'},
     11 => +{'name' => 'power_cal_factor', 'scale' => 10, 'unit' => '%'},
     12 => +{'name' => 'auto_wheel_cal'},
     13 => +{'name' => 'auto_power_zero'},
     14 => +{'name' => 'id'},
     15 => +{'name' => 'spd_enabled'},
     16 => +{'name' => 'cad_enabled'},
     17 => +{'name' => 'spdcad_enabled'},
     18 => +{'name' => 'power_enabled'},
     19 => +{'name' => 'crank_length', 'scale' => 2, 'offset' => -110, 'unit' => 'mm'},
     20 => +{'name' => 'enabled'},
     21 => +{'name' => 'bike_spd_ant_id_trans_type'},
     22 => +{'name' => 'bike_cad_ant_id_trans_type'},
     23 => +{'name' => 'bike_spdcad_ant_id_trans_type'},
     24 => +{'name' => 'bike_power_ant_id_trans_type'},
     35 => +{'name' => 'unknown35'}, # unknown UINT8 (array[3])
     36 => +{'name' => 'unknown36'}, # unknown ENUM
     37 => +{'name' => 'odometer_rollover'},
     38 => +{'name' => 'front_gear_num'},
     39 => +{'name' => 'front_gear'},
     40 => +{'name' => 'rear_gear_num'},
     41 => +{'name' => 'rear_gear'},
     44 => +{'name' => 'shimano_di2_enabled'},
   },

   'connectivity' => +{
     0 => +{'name' => 'bluetooth_enabled', 'type_name' => 'bool'},
     1 => +{'name' => 'bluetooth_le_enabled', 'type_name' => 'bool'},
     2 => +{'name' => 'ant_enabled', 'type_name' => 'bool'},
     3 => +{'name' => 'name'},
     4 => +{'name' => 'live_tracking_enabled', 'type_name' => 'bool'},
     5 => +{'name' => 'weather_conditions_enabled', 'type_name' => 'bool'},
     6 => +{'name' => 'weather_alerts_enabled', 'type_name' => 'bool'},
     7 => +{'name' => 'auto_activity_upload_enabled', 'type_name' => 'bool'},
     8 => +{'name' => 'course_download_enabled', 'type_name' => 'bool'},
     9 => +{'name' => 'workout_download_enabled', 'type_name' => 'bool'},
     10 => +{'name' => 'gps_ephemeris_download_enabled', 'type_name' => 'bool'},
     11 => +{'name' => 'incident_detection_enabled', 'type_name' => 'bool'},
     12 => +{'name' => 'grouptrack_enabled', 'type_name' => 'bool'},
   },

   # =================== Sport settings file messages ===================
   'zones_target' => +{
     1 => +{'name' => 'max_heart_rate', 'unit' => 'bpm'},
     2 => +{'name' => 'threshold_heart_rate', 'unit' => 'bpm'},
     3 => +{'name' => 'functional_threshold_power', 'unit' => 'watts'},
     5 => +{'name' => 'hr_calc_type', 'type_name' => 'hr_zone_calc'},
     7 => +{'name' => 'pwr_calc_type', 'type_name' => 'power_zone_calc'},
     8 => +{'name' => 'unknown8'}, # unknown UINT16
     9 => +{'name' => 'unknown9'}, # unknown ENUM
     10 => +{'name' => 'unknown10'}, # unknown ENUM
     13 => +{'name' => 'unknown13'}, # unknown ENUM
   },

   'sport' => +{
     0 => +{'name' => 'sport', 'type_name' => 'sport'},
     1 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     3 => +{'name' => 'name'},
     4 => +{'name' => 'unknown4'}, # unknown UINT16
     5 => +{'name' => 'unknown5'}, # unknown ENUM
     6 => +{'name' => 'unknown6'}, # unknown ENUM
     7 => +{'name' => 'unknown7'}, # unknown UINT8
     8 => +{'name' => 'unknown8'}, # unknown UINT8
     9 => +{'name' => 'unknown9'}, # unknown UINT8
     10 => +{'name' => 'unknown10'}, # unknown UINT8 (array[3])
     12 => +{'name' => 'unknown12'}, # unknown UINT8
   },

   'hr_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'high_bpm', 'unit' => 'bpm'},
     2 => +{'name' => 'name'},
   },

   'speed_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'high_value', 'scale' => 1000, 'unit' => 'm/s'},
     1 => +{'name' => 'name'},
   },

   'cadence_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'high_value', 'unit' => 'rpm'},
     1 => +{'name' => 'name'},
   },

   'power_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'high_value', 'unit' => 'watts'},
     2 => +{'name' => 'name'},
   },

   'met_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'high_bpm', 'unit' => 'bpm'},
     2 => +{'name' => 'calories', 'scale' => 10, 'unit' => 'kcal/min'},
     3 => +{'name' => 'fat_calories', 'scale' => 10, 'unit' => 'kcal/min'},
   },

   # =================== Goals file messages ===================
   'goal' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'sport', 'type_name' => 'sport'},
     1 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     2 => +{'name' => 'start_date', 'type_name' => 'date_time'},
     3 => +{'name' => 'end_date', 'type_name' => 'date_time'},
     4 => +{'name' => 'type', 'type_name' => 'goal'},
     5 => +{'name' => 'value'},
     6 => +{'name' => 'repeat', 'type_name' => 'bool'},
     7 => +{'name' => 'target_value'},
     8 => +{'name' => 'recurrence', 'type_name' => 'goal_recurrence'},
     9 => +{'name' => 'recurrence_value'},
     10 => +{'name' => 'enabled', 'type_name' => 'bool'},
   },

   # =================== Activity file messages ===================
   'activity' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'total_timer_time', 'scale' => 1000, 'unit' => 's'},
     1 => +{'name' => 'num_sessions'},
     2 => +{'name' => 'type', 'type_name' => 'activity'},
     3 => +{'name' => 'event', 'type_name' => 'event'},
     4 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     5 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
     6 => +{'name' => 'event_group'},
   },

   'session' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     2 => +{'name' => 'start_time', 'type_name' => 'date_time'},
     3 => +{'name' => 'start_position_lat', 'unit' => 'semicircles'},
     4 => +{'name' => 'start_position_long', 'unit' => 'semicircles'},
     5 => +{'name' => 'sport', 'type_name' => 'sport'},
     6 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     7 => +{'name' => 'total_elapsed_time', 'scale' => 1000, 'unit' => 's'},
     8 => +{'name' => 'total_timer_time', 'scale' => 1000, 'unit' => 's'},
     9 => +{'name' => 'total_distance', 'scale' => 100, 'unit' => 'm'},

     10 => +{
       'name' => 'total_cycles', 'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'total_steps', 'unit' => 'steps'},
	 'running' => +{'name' => 'total_strides', 'unit' => 'strides'},
	 'swimming' => +{'name' => 'total_strokes', 'unit' => 'strokes'},
       },
     },

     11 => +{'name' => 'total_calories', 'unit' => 'kcal'},
     13 => +{'name' => 'total_fat_calories', 'unit' => 'kcal'},
     14 => +{'name' => 'avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     15 => +{'name' => 'max_speed', 'scale' => 1000, 'unit' => 'm/s'},
     16 => +{'name' => 'avg_heart_rate', 'unit' => 'bpm'},
     17 => +{'name' => 'max_heart_rate', 'unit' => 'bpm'},

     18 => +{
       'name' => 'avg_cadence', 'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'avg_walking_cadence', 'unit' => 'steps/min'},
	 'running' => +{'name' => 'avg_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'avg_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     19 => +{
       'name' => 'max_cadence', 'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'max_walking_cadence', 'unit' => 'steps/min'},
	 'running' => +{'name' => 'max_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'max_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     20 => +{'name' => 'avg_power', 'unit' => 'watts'},
     21 => +{'name' => 'max_power', 'unit' => 'watts'},
     22 => +{'name' => 'total_ascent', 'unit' => 'm'},
     23 => +{'name' => 'total_descent', 'unit' => 'm'},
     24 => +{'name' => 'total_training_effect', 'scale' => 10},
     25 => +{'name' => 'first_lap_index'},
     26 => +{'name' => 'num_laps'},
     27 => +{'name' => 'event_group'},
     28 => +{'name' => 'trigger', 'type_name' => 'session_trigger'},
     29 => +{'name' => 'nec_lat', 'unit' => 'semicircles'},
     30 => +{'name' => 'nec_long', 'unit' => 'semicircles'},
     31 => +{'name' => 'swc_lat', 'unit' => 'semicircles'},
     32 => +{'name' => 'swc_long', 'unit' => 'semicircles'},
     34 => +{'name' => 'normalized_power', 'unit' => 'watts'},
     35 => +{'name' => 'training_stress_score', 'scale' => 10, 'unit' => 'tss'},
     36 => +{'name' => 'intensity_factor', 'scale' => 1000, 'unit' => 'if'},
     37 => +{'name' => 'left_right_balance', 'type_name' => 'left_right_balance_100'},
     41 => +{'name' => 'avg_stroke_count', 'scale' => 10, 'unit' => 'strokes/lap'},
     42 => +{'name' => 'avg_stroke_distance', 'scale' => 100, 'unit' => 'm'},
     43 => +{'name' => 'swim_stroke', 'type_name' => 'swim_stroke'},
     44 => +{'name' => 'pool_length', 'scale' => 100, 'unit' => 'm'},
     45 => +{'name' => 'threshold_power', 'unit' => 'watts'},
     46 => +{'name' => 'pool_length_unit', 'type_name' => 'display_measure'},
     47 => +{'name' => 'num_active_lengths', 'unit' => 'lengths'},
     48 => +{'name' => 'total_work', 'unit' => 'J'},
     49 => +{'name' => 'avg_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     50 => +{'name' => 'max_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     51 => +{'name' => 'gps_accuracy', 'unit' => 'm'},
     52 => +{'name' => 'avg_grade', 'scale' => 100, 'unit' => '%'},
     53 => +{'name' => 'avg_pos_grade', 'scale' => 100, 'unit' => '%'},
     54 => +{'name' => 'avg_neg_grade', 'scale' => 100, 'unit' => '%'},
     55 => +{'name' => 'max_pos_grade', 'scale' => 100, 'unit' => '%'},
     56 => +{'name' => 'max_neg_grade', 'scale' => 100, 'unit' => '%'},
     57 => +{'name' => 'avg_temperature', 'unit' => 'deg.C'},
     58 => +{'name' => 'max_temperature', 'unit' => 'deg.C'},
     59 => +{'name' => 'total_moving_time', 'scale' => 1000, 'unit' => 's'},
     60 => +{'name' => 'avg_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     61 => +{'name' => 'avg_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     62 => +{'name' => 'max_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     63 => +{'name' => 'max_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     64 => +{'name' => 'min_heart_rate', 'unit' => 'bpm'},
     65 => +{'name' => 'time_in_hr_zone', 'scale' => 1000, 'unit' => 's'},
     66 => +{'name' => 'time_in_speed_zone', 'scale' => 1000, 'unit' => 's'},
     67 => +{'name' => 'time_in_cadence_zone', 'scale' => 1000, 'unit' => 's'},
     68 => +{'name' => 'time_in_power_zone', 'scale' => 1000, 'unit' => 's'},
     69 => +{'name' => 'avg_lap_time', 'scale' => 1000, 'unit' => 's'},
     70 => +{'name' => 'best_lap_index'},
     71 => +{'name' => 'min_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     78 => +{'name' => 'unknown78'}, # unknown UINT32
     81 => +{'name' => 'unknown81'}, # unknown ENUM
     82 => +{'name' => 'player_score'},
     83 => +{'name' => 'opponent_score'},
     84 => +{'name' => 'opponent_name'},
     85 => +{'name' => 'stroke_count', 'unit' => 'counts'},
     86 => +{'name' => 'zone_count', 'unit' => 'counts'},
     87 => +{'name' => 'max_ball_speed', 'scale' => 100, 'unit' => 'm/s'},
     88 => +{'name' => 'avg_ball_speed', 'scale' => 100, 'unit' => 'm/s'},
     89 => +{'name' => 'avg_vertical_oscillation', 'scale' => 10, 'unit' => 'mm'},
     90 => +{'name' => 'avg_stance_time_percent', 'scale' => 100, 'unit' => '%'},
     91 => +{'name' => 'avg_stance_time', 'scale' => 10, 'unit' => 'ms'},
     92 => +{'name' => 'avg_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     93 => +{'name' => 'max_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     94 => +{'name' => 'total_fractional_cycles', 'scale' => 128, 'unit' => 'cycles'},
     95 => +{'name' => 'avg_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     96 => +{'name' => 'min_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     97 => +{'name' => 'max_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     98 => +{'name' => 'avg_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     99 => +{'name' => 'min_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     100 => +{'name' => 'max_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     101 => +{'name' => 'avg_left_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     102 => +{'name' => 'avg_right_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     103 => +{'name' => 'avg_left_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     104 => +{'name' => 'avg_right_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     105 => +{'name' => 'avg_combined_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     106 => +{'name' => 'unknown106'}, # unknown UINT16
     107 => +{'name' => 'unknown107'}, # unknown UINT16
     108 => +{'name' => 'unknown108'}, # unknown UINT16
     109 => +{'name' => 'unknown109'}, # unknown UINT8
     110 => +{'name' => 'unknown110'}, # unknown STRING
     111 => +{'name' => 'sport_index'},
     112 => +{'name' => 'time_standing', 'scale' => 1000, 'unit' => 's'},
     113 => +{'name' => 'stand_count'},
     114 => +{'name' => 'avg_left_pco', 'unit' => 'mm'},
     115 => +{'name' => 'avg_right_pco', 'unit' => 'mm'},
     116 => +{'name' => 'avg_left_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     117 => +{'name' => 'avg_left_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     118 => +{'name' => 'avg_right_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     119 => +{'name' => 'avg_right_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     120 => +{'name' => 'avg_power_position', 'unit' => 'watts'},
     121 => +{'name' => 'max_power_position', 'unit' => 'watts'},
     122 => +{'name' => 'avg_cadence_position', 'unit' => 'rpm'},
     123 => +{'name' => 'max_cadence_position', 'unit' => 'rpm'},
     124 => +{'name' => 'enhanced_avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     125 => +{'name' => 'enhanced_max_speed', 'scale' => 1000, 'unit' => 'm/s'},
     126 => +{'name' => 'enhanced_avg_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     127 => +{'name' => 'enhanced_min_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     128 => +{'name' => 'enhanced_max_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     129 => +{'name' => 'avg_lev_motor_power', 'unit' => 'watts'},
     130 => +{'name' => 'max_lev_motor_power', 'unit' => 'watts'},
     131 => +{'name' => 'lev_battery_consumption', 'scale' => 2, 'unit' => '%'},
     132 => +{'name' => 'avg_vertical_ratio', 'scale' => 100, 'unit' => '%'},
     133 => +{'name' => 'avg_stance_time_balance', 'scale' => 100, 'unit' => '%'},
     134 => +{'name' => 'avg_step_length', 'scale' => 10, 'unit' => 'mm'},
   },

   'lap' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     2 => +{'name' => 'start_time', 'type_name' => 'date_time'},
     3 => +{'name' => 'start_position_lat', 'unit' => 'semicircles'},
     4 => +{'name' => 'start_position_long', 'unit' => 'semicircles'},
     5 => +{'name' => 'end_position_lat', 'unit' => 'semicircles'},
     6 => +{'name' => 'end_position_long', 'unit' => 'semicircles'},
     7 => +{'name' => 'total_elapsed_time', 'scale' => 1000, 'unit' => 's'},
     8 => +{'name' => 'total_timer_time', 'scale' => 1000, 'unit' => 's'},
     9 => +{'name' => 'total_distance', 'scale' => 100, 'unit' => 'm'},

     10 => +{
       'name' => 'total_cycles', 'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'total_steps', 'unit' => 'steps'},
	 'running' => +{'name' => 'total_strides', 'unit' => 'strides'},
	 'swimming' => +{'name' => 'total_strokes', 'unit' => 'strokes'},
       },
     },

     11 => +{'name' => 'total_calories', 'unit' => 'kcal'},
     12 => +{'name' => 'total_fat_calories', 'unit' => 'kcal'},
     13 => +{'name' => 'avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     14 => +{'name' => 'max_speed', 'scale' => 1000, 'unit' => 'm/s'},
     15 => +{'name' => 'avg_heart_rate', 'unit' => 'bpm'},
     16 => +{'name' => 'max_heart_rate', 'unit' => 'bpm'},

     17 => +{
       'name' => 'avg_cadence', 'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'avg_walking_cadence', 'unit' => 'steps/min'},
	 'running' => +{'name' => 'avg_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'avg_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     18 => +{
       'name' => 'max_cadence', 'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'max_walking_cadence', 'unit' => 'steps/min'},
	 'running' => +{'name' => 'max_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'max_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     19 => +{'name' => 'avg_power', 'unit' => 'watts'},
     20 => +{'name' => 'max_power', 'unit' => 'watts'},
     21 => +{'name' => 'total_ascent', 'unit' => 'm'},
     22 => +{'name' => 'total_descent', 'unit' => 'm'},
     23 => +{'name' => 'intensity', 'type_name' => 'intensity'},
     24 => +{'name' => 'lap_trigger', 'type_name' => 'lap_trigger'},
     25 => +{'name' => 'sport', 'type_name' => 'sport'},
     26 => +{'name' => 'event_group'},
     27 => +{'name' => 'nec_lat', 'unit' => 'semicircles'}, # not present?
     28 => +{'name' => 'nec_long', 'unit' => 'semicircles'}, # not present?
     29 => +{'name' => 'swc_lat', 'unit' => 'semicircles'}, # not present?
     30 => +{'name' => 'swc_long', 'unit' => 'semicircles'}, # not present?
     32 => +{'name' => 'num_lengths', 'unit' => 'lengths'},
     33 => +{'name' => 'normalized_power', 'unit' => 'watts'},
     34 => +{'name' => 'left_right_balance', 'type_name' => 'left_right_balance_100'},
     35 => +{'name' => 'first_length_index'},
     37 => +{'name' => 'avg_stroke_distance', 'scale' => 100, 'unit' => 'm'},
     38 => +{'name' => 'swim_stroke', 'type_name' => 'swim_stroke'},
     39 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     40 => +{'name' => 'num_active_lengths', 'unit' => 'lengths'},
     41 => +{'name' => 'total_work', 'unit' => 'J'},
     42 => +{'name' => 'avg_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     43 => +{'name' => 'max_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     44 => +{'name' => 'gps_accuracy', 'unit' => 'm'},
     45 => +{'name' => 'avg_grade', 'scale' => 100, 'unit' => '%'},
     46 => +{'name' => 'avg_pos_grade', 'scale' => 100, 'unit' => '%'},
     47 => +{'name' => 'avg_neg_grade', 'scale' => 100, 'unit' => '%'},
     48 => +{'name' => 'max_pos_grade', 'scale' => 100, 'unit' => '%'},
     49 => +{'name' => 'max_neg_grade', 'scale' => 100, 'unit' => '%'},
     50 => +{'name' => 'avg_temperature', 'unit' => 'deg.C'},
     51 => +{'name' => 'max_temperature', 'unit' => 'deg.C'},
     52 => +{'name' => 'total_moving_time', 'scale' => 1000, 'unit' => 's'},
     53 => +{'name' => 'avg_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     54 => +{'name' => 'avg_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     55 => +{'name' => 'max_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     56 => +{'name' => 'max_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     57 => +{'name' => 'time_in_hr_zone', 'scale' => 1000, 'unit' => 's'},
     58 => +{'name' => 'time_in_speed_zone', 'scale' => 1000, 'unit' => 's'},
     59 => +{'name' => 'time_in_cadence_zone', 'scale' => 1000, 'unit' => 's'},
     60 => +{'name' => 'time_in_power_zone', 'scale' => 1000, 'unit' => 's'},
     61 => +{'name' => 'repetition_num'},
     62 => +{'name' => 'min_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     63 => +{'name' => 'min_heart_rate', 'unit' => 'bpm'},
     70 => +{'name' => 'unknown70'}, # unknown UINT32
     71 => +{'name' => 'wkt_step_index', 'type_name' => 'message_index'},
     72 => +{'name' => 'unknown72'}, # unknown ENUM
     74 => +{'name' => 'opponent_score'},
     75 => +{'name' => 'stroke_count', 'unit' => 'counts'},
     76 => +{'name' => 'zone_count', 'unit' => 'counts'},
     77 => +{'name' => 'avg_vertical_oscillation', 'scale' => 10, 'unit' => 'mm'},
     78 => +{'name' => 'avg_stance_time_percent', 'scale' => 100, 'unit' => '%'},
     79 => +{'name' => 'avg_stance_time', 'scale' => 10, 'unit' => 'ms'},
     80 => +{'name' => 'avg_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     81 => +{'name' => 'max_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     82 => +{'name' => 'total_fractional_cycles', 'scale' => 128, 'unit' => 'cycles'},
     83 => +{'name' => 'player_score'},
     84 => +{'name' => 'avg_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     85 => +{'name' => 'min_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     86 => +{'name' => 'max_total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     87 => +{'name' => 'avg_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     88 => +{'name' => 'min_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     89 => +{'name' => 'max_saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     91 => +{'name' => 'avg_left_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     92 => +{'name' => 'avg_right_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     93 => +{'name' => 'avg_left_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     94 => +{'name' => 'avg_right_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     95 => +{'name' => 'avg_combined_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     96 => +{'name' => 'unknown96'}, # unknown UINT16
     97 => +{'name' => 'unknown97'}, # unknown UINT16
     98 => +{'name' => 'time_standing', 'scale' => 1000, 'unit' => 's'},
     99 => +{'name' => 'stand_count'},
     100 => +{'name' => 'avg_left_pco', 'unit' => 'mm'},
     101 => +{'name' => 'avg_right_pco', 'unit' => 'mm'},
     102 => +{'name' => 'avg_left_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     103 => +{'name' => 'avg_left_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     104 => +{'name' => 'avg_right_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     105 => +{'name' => 'avg_right_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     106 => +{'name' => 'avg_power_position', 'unit' => 'watts'},
     107 => +{'name' => 'max_power_position', 'unit' => 'watts'},
     108 => +{'name' => 'avg_cadence_position', 'unit' => 'rpm'},
     109 => +{'name' => 'max_cadence_position', 'unit' => 'rpm'},
     110 => +{'name' => 'enhanced_avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     111 => +{'name' => 'enhanced_max_speed', 'scale' => 1000, 'unit' => 'm/s'},
     112 => +{'name' => 'enhanced_avg_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     113 => +{'name' => 'enhanced_min_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     114 => +{'name' => 'enhanced_max_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     115 => +{'name' => 'avg_lev_motor_power', 'unit' => 'watts'},
     116 => +{'name' => 'max_lev_motor_power', 'unit' => 'watts'},
     117 => +{'name' => 'lev_battery_consumption', 'scale' => 2, 'unit' => '%'},
     118 => +{'name' => 'avg_vertical_ratio', 'scale' => 100, 'unit' => '%'},
     119 => +{'name' => 'avg_stance_time_balance', 'scale' => 100, 'unit' => '%'},
     120 => +{'name' => 'avg_step_length', 'scale' => 10, 'unit' => 'mm'},
   },

   'length' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     2 => +{'name' => 'start_time', 'type_name' => 'date_time'},
     3 => +{'name' => 'total_elapsed_time', 'scale' => 1000, 'unit' => 's'},
     4 => +{'name' => 'total_timer_time', 'scale' => 1000, 'unit' => 's'},
     5 => +{'name' => 'total_strokes', 'unit' => 'strokes'},
     6 => +{'name' => 'avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     7 => +{'name' => 'swim_stroke', 'type_name' => 'swim_stroke'},
     9 => +{'name' => 'avg_swimming_cadence', 'unit' => 'strokes/min'},
     10 => +{'name' => 'event_group'},
     11 => +{'name' => 'total_calories', 'unit' => 'kcal'},
     12 => +{'name' => 'length_type', 'type_name' => 'length_type'},
     18 => +{'name' => 'player_score'},
     19 => +{'name' => 'opponent_score'},
     20 => +{'name' => 'stroke_count', 'unit' => 'counts'},
     21 => +{'name' => 'zone_count', 'unit' => 'counts'},
   },

   'record' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     1 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     2 => +{'name' => 'altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     3 => +{'name' => 'heart_rate', 'unit' => 'bpm'},
     4 => +{'name' => 'cadence', 'unit' => 'rpm'},
     5 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     6 => +{'name' => 'speed', 'scale' => 1000, 'unit' => 'm/s'},
     7 => +{'name' => 'power', 'unit' => 'watts'},
     8 => +{'name' => 'compressed_speed_distance'}, # complex decoding!
     9 => +{'name' => 'grade', 'scale' => 100, 'unit' => '%'},
     10 => +{'name' => 'resistance'},
     11 => +{'name' => 'time_from_course', 'scale' => 1000, 'unit' => 's'},
     12 => +{'name' => 'cycle_length', 'scale' => 100, 'unit' => 'm'},
     13 => +{'name' => 'temperature', 'unit' => 'deg.C'},
     17 => +{'name' => 'speed_1s', 'scale' => 16, 'unit' => 'm/s'},
     18 => +{'name' => 'cycles', 'unit' => 'cycles'},
     19 => +{'name' => 'total_cycles', 'unit' => 'cycles'},
     28 => +{'name' => 'compressed_accumulated_power', 'unit' => 'watts'},
     29 => +{'name' => 'accumulated_power', 'unit' => 'watts'},
     30 => +{'name' => 'left_right_balance', 'type_name' => 'left_right_balance'},
     31 => +{'name' => 'gps_accuracy', 'unit' => 'm'},
     32 => +{'name' => 'vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     33 => +{'name' => 'calories', 'scale' => 1, 'unit' => 'kcal'},
     39 => +{'name' => 'vertical_oscillation', 'scale' => 10, 'unit' => 'mm'},
     40 => +{'name' => 'stance_time_percent', 'scale' => 100, 'unit' => '%'},
     41 => +{'name' => 'stance_time', 'scale' => 10, 'unit' => 'ms'},
     42 => +{'name' => 'activity_type', 'type_name' => 'activity_type'},
     43 => +{'name' => 'left_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     44 => +{'name' => 'right_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     45 => +{'name' => 'left_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     46 => +{'name' => 'right_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     47 => +{'name' => 'combined_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     48 => +{'name' => 'time128', 'scale' => 128, 'unit' => 's'},
     49 => +{'name' => 'stroke_type', 'type_name' => 'stroke_type'},
     50 => +{'name' => 'zone'},
     51 => +{'name' => 'ball_speed', 'scale' => 100, 'unit' => 'm/s'},
     52 => +{'name' => 'cadence256', 'scale' => 256, 'unit' => 'rpm'},
     53 => +{'name' => 'fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     54 => +{'name' => 'total_hemoglobin_conc', 'scale' => 100, 'unit' => 'g/dL'},
     55 => +{'name' => 'total_hemoglobin_conc_min', 'scale' => 100, 'unit' => 'g/dL'},
     56 => +{'name' => 'total_hemoglobin_conc_max', 'scale' => 100, 'unit' => 'g/dL'},
     57 => +{'name' => 'saturated_hemoglobin_percent', 'scale' => 10, 'unit' => '%'},
     58 => +{'name' => 'saturated_hemoglobin_percent_min', 'scale' => 10, 'unit' => '%'},
     59 => +{'name' => 'saturated_hemoglobin_percent_max', 'scale' => 10, 'unit' => '%'},
     61 => +{'name' => 'avg_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     62 => +{'name' => 'device_index', 'type_name' => 'device_index'},
     63 => +{'name' => 'max_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     66 => +{'name' => 'time_in_speed_zone', 'scale' => 1000, 'unit' => 's'},
     67 => +{'name' => 'left_pco', 'unit' => 'mm'},
     68 => +{'name' => 'right_pco', 'unit' => 'mm'},
     69 => +{'name' => 'left_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     70 => +{'name' => 'left_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     71 => +{'name' => 'right_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     72 => +{'name' => 'right_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     73 => +{'name' => 'enhanced_speed', 'scale' => 1000, 'unit' => 'm/s'},
     78 => +{'name' => 'enhanced_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     81 => +{'name' => 'battery_soc', 'scale' => 2, 'unit' => '%'},
     82 => +{'name' => 'motor_power', 'unit' => 'watts'},
     83 => +{'name' => 'vertical_ratio', 'scale' => 100, 'unit' => '%'},
     84 => +{'name' => 'stance_time_balance', 'scale' => 100, 'unit' => '%'},
     85 => +{'name' => 'step_length', 'scale' => 10, 'unit' => 'mm'},
     112 => +{'name' => 'time_standing', 'scale' => 1000, 'unit' => 's'},
   },

   'event' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     2 => +{'name' => 'data16'}, # no switch?

     3 => +{
       'name' => 'data',

       'switch' => +{
	 '_by' => 'event',
	 'timer' => +{'name' => 'timer_trigger', 'type_name' => 'timer_trigger'},
	 'course_point' => +{'name' => 'course_point_index', 'type_name' => 'message_index'},
	 'battery' => +{'name' => 'battery_level', 'scale' => 1000, 'unit' => 'V'},
	 'virtual_partner_pace' => +{'name' => 'virtual_partner_speed', 'scale' => 1000, 'unit' => 'm/s'},
	 'hr_high_alert' => +{'name' => 'hr_high_alert', 'unit' => 'bpm'},
	 'hr_low_alert' => +{'name' => 'hr_low_alert', 'unit' => 'bpm'},
	 'speed_high_alert' => +{'name' => 'speed_high_alert', 'scale' => 1000, 'unit' => 'm/s'},
	 'speed_low_alert' => +{'name' => 'speed_low_alert', 'scale' => 1000, 'unit' => 'm/s'},
	 'cad_high_alert' => +{'name' => 'cad_high_alert', 'unit' => 'rpm'},
	 'cad_low_alert' => +{'name' => 'cad_low_alert', 'unit' => 'rpm'},
	 'power_high_alert' => +{'name' => 'power_high_alert', 'unit' => 'watts'},
	 'power_low_alert' => +{'name' => 'power_low_alert', 'unit' => 'watts'},
	 'time_duration_alert' => +{'name' => 'time_duration_alert', 'scale' => 1000, 'unit' => 's'},
	 'distance_duration_alert' => +{'name' => 'distance_duration_alert', 'scale' => 100, 'unit' => 'm'},
	 'calorie_duration_alert' => +{'name' => 'calorie_duration_alert', 'unit' => 'calories'},
	 'fitness_equipment' => +{'name' => 'fitness_equipment_state', 'type_name' => 'fitness_equipment_state'},
	 'sport_point' => +{'name' => 'sport_point'},
	 'front_gear_change' => +{'name' => 'gear_change_data'},
	 'rear_gear_change' => +{'name' => 'gear_change_data'},
	 'rider_position_change' => +{'name' => 'rider_position', 'type_name' => 'rider_position_type'},
	 'comm_timeout' => +{'name' => 'comm_timeout', 'type_name' => 'comm_timeout_type'},
       },
     },

     4 => +{'name' => 'event_group'},
     7 => +{'name' => 'score'},
     8 => +{'name' => 'opponent_score'},
     9 => +{'name' => 'front_gear_num'},
     10 => +{'name' => 'front_gear'},
     11 => +{'name' => 'rear_gear_num'},
     12 => +{'name' => 'rear_gear'},
     13 => +{'name' => 'device_index', 'type_name' => 'device_index'},
   },

   'device_info' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'device_index', 'type_name' => 'device_index'},

     1 => +{
       'name' => 'device_type',

       'switch' => +{
	 '_by' => 'source_type',
	 'antplus' => +{'name' => 'antplus_device_type', 'type_name' => 'antplus_device_type'},
	 'ant' => +{'name' => 'ant_device_type'},
       },
     },

     2 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},
     3 => +{'name' => 'serial_number'},

     4 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream_oem' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     5 => +{'name' => 'software_version', 'scale' => 100},
     6 => +{'name' => 'hardware_version'},
     7 => +{'name' => 'cum_operating_time', 'unit' => 's'},
     8 => +{'name' => 'unknown8'}, # unknown UINT32
     9 => +{'name' => 'unknown9'}, # unknown UINT8
     10 => +{'name' => 'battery_voltage', 'scale' => 256, 'unit' => 'v'},
     11 => +{'name' => 'battery_status', 'type_name' => 'battery_status'},
     15 => +{'name' => 'unknown15'}, # unknown UINT32
     16 => +{'name' => 'unknown16'}, # unknown UINT32
     18 => +{'name' => 'sensor_position', 'type_name' => 'body_location'},
     19 => +{'name' => 'descriptor'},
     20 => +{'name' => 'ant_transmission_type'},
     21 => +{'name' => 'ant_device_number'},
     22 => +{'name' => 'ant_network', 'type_name' => 'ant_network'},
     24 => +{'name' => 'unknown24'}, # unknown UINT32Z
     25 => +{'name' => 'source_type', 'type_name' => 'source_type'},
     27 => +{'name' => 'product_name'},
   },

   'training_file' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'type', 'type_name' => 'file'},
     1 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     2 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream_oem' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     3 => +{'name' => 'serial_number'},
     4 => +{'name' => 'time_created', 'type_name' => 'date_time'},
   },

   'hrv' => +{ # heart rate variability
     0 => +{'name' => 'time', 'scale' => 1000, 'unit' => 's'},
   },

   'weather_conditions' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'weather_report', 'type_name' => 'weather_report'},
     1 => +{'name' => 'temperature', 'unit' => 'deg.C'},
     2 => +{'name' => 'condition', 'type_name' => 'weather_status'},
     3 => +{'name' => 'wind_direction', 'unit' => 'degrees'},
     4 => +{'name' => 'wind_speed', 'scale' => 1000, 'unit' => 'm/s'},
     5 => +{'name' => 'precipitation_probability'},
     6 => +{'name' => 'temperature_feels_like', 'unit' => 'deg.C'},
     7 => +{'name' => 'relative_humidity', 'unit' => '%'},
     8 => +{'name' => 'location'},
     9 => +{'name' => 'observed_at_time', 'type_name' => 'date_time'},
     10 => +{'name' => 'observed_location_lat', 'unit' => 'semicircles'},
     11 => +{'name' => 'observed_location_long', 'unit' => 'semicircles'},
     12 => +{'name' => 'day_of_week', 'type_name' => 'day_of_week'},
     13 => +{'name' => 'high_temperature', 'unit' => 'deg.C'},
     14 => +{'name' => 'low_temperature', 'unit' => 'deg.C'},
   },

   'weather_alert' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'report_id'},
     1 => +{'name' => 'issue_time', 'type_name' => 'date_time'},
     2 => +{'name' => 'expire_time', 'type_name' => 'date_time'},
     3 => +{'name' => 'severity', 'type_name' => 'weather_severity'},
     4 => +{'name' => 'type', 'type_name' => 'weather_severe_type'},
   },

   'gps_metadata' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     2 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     3 => +{'name' => 'enhanced_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     4 => +{'name' => 'enhanced_speed', 'scale' => 1000, 'unit' => 'm/s'},
     5 => +{'name' => 'heading', 'scale' => 100, 'unit' => 'degrees'},
     6 => +{'name' => 'utc_timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     7 => +{'name' => 'velocity', 'scale' => 100, 'unit' => 'm/s'},
   },

   'camera_event' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'camera_event_type', 'type_name' => 'camera_event_type'},
     2 => +{'name' => 'camera_file_uuid'},
     3 => +{'name' => 'camera_orientation', 'type_name' => 'camera_orientation_type'},
   },

   'gyroscope_data' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'sample_time_offset', 'unit' => 'ms'},
     2 => +{'name' => 'gyro_x', 'unit' => 'counts'},
     3 => +{'name' => 'gyro_y', 'unit' => 'counts'},
     4 => +{'name' => 'gyro_z', 'unit' => 'counts'},
     5 => +{'name' => 'calibrated_gyro_x', 'unit' => 'deg/s'},
     6 => +{'name' => 'calibrated_gyro_y', 'unit' => 'deg/s'},
     7 => +{'name' => 'calibrated_gyro_z', 'unit' => 'deg/s'},
   },

   'accelerometer_data' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'sample_time_offset', 'unit' => 'ms'},
     2 => +{'name' => 'accel_x', 'unit' => 'counts'},
     3 => +{'name' => 'accel_y', 'unit' => 'counts'},
     4 => +{'name' => 'accel_z', 'unit' => 'counts'},
     5 => +{'name' => 'calibrated_accel_x', 'unit' => 'g'},
     6 => +{'name' => 'calibrated_accel_y', 'unit' => 'g'},
     7 => +{'name' => 'calibrated_accel_z', 'unit' => 'g'},
   },

   'magnetometer_data' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'sample_time_offset', 'unit' => 'ms'},
     2 => +{'name' => 'mag_x', 'unit' => 'counts'},
     3 => +{'name' => 'mag_y', 'unit' => 'counts'},
     4 => +{'name' => 'mag_z', 'unit' => 'counts'},
     5 => +{'name' => 'calibrated_mag_x', 'unit' => 'G'},
     6 => +{'name' => 'calibrated_mag_y', 'unit' => 'G'},
     7 => +{'name' => 'calibrated_mag_z', 'unit' => 'G'},
   },

   'three_d_sensor_calibration' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'sensor_type', 'type_name' => 'sensor_type'},

     1 => +{
       'name' => 'calibration_factor',

       'switch' => +{
	 '_by' => 'sensor_type',
	 'accelerometer' => +{'name' => 'accel_cal_factor'},
	 'gyroscope' => +{'name' => 'gyro_cal_factor'},
       },
     },

     2 => +{'name' => 'calibration_divisor', 'unit' => 'counts'},
     3 => +{'name' => 'level_shift'},
     4 => +{'name' => 'offset_cal'},
     5 => +{'name' => 'orientation_matrix', 'scale' => 65535},
   },

   'video_frame' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'frame_number'},
   },

   'obdii_data' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'time_offset', 'unit' => 'ms'},
     2 => +{'name' => 'pid'},
     3 => +{'name' => 'raw_data'},
     4 => +{'name' => 'pid_data_size'},
     5 => +{'name' => 'system_time'},
     6 => +{'name' => 'start_timestamp', 'type_name' => 'date_time'},
     7 => +{'name' => 'start_timestamp_ms', 'unit' => 'ms'},
   },

   'nmea_sentence' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'sentence'},
   },

   'aviation_attitude' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timestamp_ms', 'unit' => 'ms'},
     1 => +{'name' => 'system_time', 'unit' => 'ms'},
     2 => +{'name' => 'pitch', 'scale' => 10430.38, 'unit' => 'radians'},
     3 => +{'name' => 'roll', 'scale' => 10430.38, 'unit' => 'radians'},
     4 => +{'name' => 'accel_lateral', 'scale' => 100, 'unit' => 'm/s^2'},
     5 => +{'name' => 'accel_normal', 'scale' => 100, 'unit' => 'm/s^2'},
     6 => +{'name' => 'turn_rate', 'scale' => 1024, 'unit' => 'radians/second'},
     7 => +{'name' => 'stage', 'type_name' => 'attitude_stage'},
     8 => +{'name' => 'attitude_stage_complete', 'unit' => '%'},
     9 => +{'name' => 'track', 'scale' => 10430.38, 'unit' => 'radians'},
     10 => +{'name' => 'validity', 'type_name' => 'attitude_validity'},
   },

   'video' => +{
     0 => +{'name' => 'url'},
     1 => +{'name' => 'hosting_provider'},
     2 => +{'name' => 'duration', 'unit' => 'ms'},
   },

   'video_title' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'message_count'},
     1 => +{'name' => 'text'},
   },

   'video_description' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'message_count'},
     1 => +{'name' => 'text'},
   },

   'video_clip' => +{
     0 => +{'name' => 'clip_number'},
     1 => +{'name' => 'start_timestamp', 'type_name' => 'date_time'},
     2 => +{'name' => 'start_timestamp_ms', 'unit' => 'ms'},
     3 => +{'name' => 'end_timestamp', 'type_name' => 'date_time'},
     4 => +{'name' => 'end_timestamp_ms', 'unit' => 'ms'},
     6 => +{'name' => 'clip_start', 'unit' => 'ms'},
     7 => +{'name' => 'clip_end', 'unit' => 'ms'},
   },

   # =================== Course file messages ===================
   'course' => +{
     4 => +{'name' => 'sport', 'type_name' => 'sport'},
     5 => +{'name' => 'name'},
     6 => +{'name' => 'capabilities', 'type_name' => 'course_capabilities'},
   },

   'course_point' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     2 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     3 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     4 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     5 => +{'name' => 'type', 'type_name' => 'course_point'},
     6 => +{'name' => 'name'},
     8 => +{'name' => 'favorite'},
   },

   # =================== Segment file messages ===================
   'segment_id' => +{
     0 => +{'name' => 'name'},
     1 => +{'name' => 'uuid'},
     2 => +{'name' => 'sport', 'type_name' => 'sport'},
     3 => +{'name' => 'enabled'},
     4 => +{'name' => 'user_profile_primary_key'},
     5 => +{'name' => 'device_id'},
     6 => +{'name' => 'default_race_leader'},
     7 => +{'name' => 'delete_status', 'type_name' => 'segment_delete_status'},
     8 => +{'name' => 'selection_type', 'type_name' => 'segment_selection_type'},
   },

   'segment_leaderboard_entry' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'name'},
     1 => +{'name' => 'type', 'type_name' => 'segment_leaderboard_type'},
     2 => +{'name' => 'group_primary_key'},
     3 => +{'name' => 'activity_id'},
     4 => +{'name' => 'segment_time', 'scale' => 1000, 'unit' => 's'},
     5 => +{'name' => 'activity_id_string'},
   },

   'segment_point' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     2 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     3 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     4 => +{'name' => 'altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     5 => +{'name' => 'leader_time', 'scale' => 1000, 'unit' => 's'},
   },

   'segment_lap' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},
     2 => +{'name' => 'start_time', 'type_name' => 'date_time'},
     3 => +{'name' => 'start_position_lat', 'unit' => 'semicircles'},
     4 => +{'name' => 'start_position_long', 'unit' => 'semicircles'},
     5 => +{'name' => 'end_position_lat', 'unit' => 'semicircles'},
     6 => +{'name' => 'end_position_long', 'unit' => 'semicircles'},
     7 => +{'name' => 'total_elapsed_time', 'scale' => 1000, 'unit' => 's'},
     8 => +{'name' => 'total_timer_time', 'scale' => 1000, 'unit' => 's'},
     9 => +{'name' => 'total_distance', 'scale' => 100, 'unit' => 'm'},

     10 => +{
       'name' => 'total_cycles', 'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'sport',
	 'walking' => +{'name' => 'total_steps', 'unit' => 'steps'},
	 'running' => +{'name' => 'total_strides', 'unit' => 'strides'},
	 'swimming' => +{'name' => 'total_strokes', 'unit' => 'strokes'},
       },
     },

     11 => +{'name' => 'total_calories', 'unit' => 'kcal'},
     12 => +{'name' => 'total_fat_calories', 'unit' => 'kcal'},
     13 => +{'name' => 'avg_speed', 'scale' => 1000, 'unit' => 'm/s'},
     14 => +{'name' => 'max_speed', 'scale' => 1000, 'unit' => 'm/s'},
     15 => +{'name' => 'avg_heart_rate', 'unit' => 'bpm'},
     16 => +{'name' => 'max_heart_rate', 'unit' => 'bpm'},
     17 => +{'name' => 'avg_cadence', 'unit' => 'rpm'},
     18 => +{'name' => 'max_cadence', 'unit' => 'rpm'},
     19 => +{'name' => 'avg_power', 'unit' => 'watts'},
     20 => +{'name' => 'max_power', 'unit' => 'watts'},
     21 => +{'name' => 'total_ascent', 'unit' => 'm'},
     22 => +{'name' => 'total_descent', 'unit' => 'm'},
     23 => +{'name' => 'sport', 'type_name' => 'sport'},
     24 => +{'name' => 'event_group'},
     25 => +{'name' => 'nec_lat', 'unit' => 'semicircles'},
     26 => +{'name' => 'nec_long', 'unit' => 'semicircles'},
     27 => +{'name' => 'swc_lat', 'unit' => 'semicircles'},
     28 => +{'name' => 'swc_long', 'unit' => 'semicircles'},
     29 => +{'name' => 'name'},
     30 => +{'name' => 'normalized_power', 'unit' => 'watts'},
     31 => +{'name' => 'left_right_balance', 'type_name' => 'left_right_balance_100'},
     32 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     33 => +{'name' => 'total_work', 'unit' => 'J'},
     34 => +{'name' => 'avg_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     35 => +{'name' => 'max_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     36 => +{'name' => 'gps_accuracy', 'unit' => 'm'},
     37 => +{'name' => 'avg_grade', 'scale' => 100, 'unit' => '%'},
     38 => +{'name' => 'avg_pos_grade', 'scale' => 100, 'unit' => '%'},
     39 => +{'name' => 'avg_neg_grade', 'scale' => 100, 'unit' => '%'},
     40 => +{'name' => 'max_pos_grade', 'scale' => 100, 'unit' => '%'},
     41 => +{'name' => 'max_neg_grade', 'scale' => 100, 'unit' => '%'},
     42 => +{'name' => 'avg_temperature', 'unit' => 'deg.C'},
     43 => +{'name' => 'max_temperature', 'unit' => 'deg.C'},
     44 => +{'name' => 'total_moving_time', 'scale' => 1000, 'unit' => 's'},
     45 => +{'name' => 'avg_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     46 => +{'name' => 'avg_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     47 => +{'name' => 'max_pos_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     48 => +{'name' => 'max_neg_vertical_speed', 'scale' => 1000, 'unit' => 'm/s'},
     49 => +{'name' => 'time_in_hr_zone', 'scale' => 1000, 'unit' => 's'},
     50 => +{'name' => 'time_in_speed_zone', 'scale' => 1000, 'unit' => 's'},
     51 => +{'name' => 'time_in_cadence_zone', 'scale' => 1000, 'unit' => 's'},
     52 => +{'name' => 'time_in_power_zone', 'scale' => 1000, 'unit' => 's'},
     53 => +{'name' => 'repetition_num'},
     54 => +{'name' => 'min_altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     55 => +{'name' => 'min_heart_rate', 'unit' => 'bpm'},
     56 => +{'name' => 'active_time', 'scale' => 1000, 'unit' => 's'},
     57 => +{'name' => 'wkt_step_index', 'type_name' => 'message_index'},
     58 => +{'name' => 'sport_event', 'type_name' => 'sport_event'},
     59 => +{'name' => 'avg_left_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     60 => +{'name' => 'avg_right_torque_effectiveness', 'scale' => 2, 'unit' => '%'},
     61 => +{'name' => 'avg_left_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     62 => +{'name' => 'avg_right_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     63 => +{'name' => 'avg_combined_pedal_smoothness', 'scale' => 2, 'unit' => '%'},
     64 => +{'name' => 'status', 'type_name' => 'segment_lap_status'},
     65 => +{'name' => 'uuid'},
     66 => +{'name' => 'avg_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     67 => +{'name' => 'max_fractional_cadence', 'scale' => 128, 'unit' => 'rpm'},
     68 => +{'name' => 'total_fractional_cycles', 'scale' => 128, 'unit' => 'cycles'},
     69 => +{'name' => 'front_gear_shift_count'},
     70 => +{'name' => 'rear_gear_shift_count'},
     71 => +{'name' => 'time_standing', 'scale' => 1000, 'unit' => 's'},
     72 => +{'name' => 'stand_count'},
     73 => +{'name' => 'avg_left_pco', 'unit' => 'mm'},
     74 => +{'name' => 'avg_right_pco', 'unit' => 'mm'},
     75 => +{'name' => 'avg_left_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     76 => +{'name' => 'avg_left_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     77 => +{'name' => 'avg_right_power_phase', 'scale' => 0.7111111, 'unit' => 'degrees'},
     78 => +{'name' => 'avg_right_power_phase_peak', 'scale' => 0.7111111, 'unit' => 'degrees'},
     79 => +{'name' => 'avg_power_position', 'unit' => 'watts'},
     80 => +{'name' => 'max_power_position', 'unit' => 'watts'},
     81 => +{'name' => 'avg_cadence_position', 'unit' => 'rpm'},
     82 => +{'name' => 'max_cadence_position', 'unit' => 'rpm'},
   },

   # =================== Segment list file messages ===================
   'segment_file' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'file_uuid'},
     3 => +{'name' => 'enabled'},
     4 => +{'name' => 'user_profile_primary_key'},
     7 => +{'name' => 'leader_type', 'type_name' => 'segment_leaderboard_type'},
     8 => +{'name' => 'leader_group_primary_key'},
     9 => +{'name' => 'leader_activity_id'},
     10 => +{'name' => 'leader_activity_id_string'},
     11 => +{'name' => 'default_race_leader'},
   },

   # =================== Workout file messages ===================
   'workout' => +{
     4 => +{'name' => 'sport', 'type_name' => 'sport'},
     5 => +{'name' => 'capabilities', 'type_name' => 'workout_capabilities'},
     6 => +{'name' => 'num_valid_steps'},
     7 => +{'name' => 'protection'},
     8 => +{'name' => 'wkt_name'},
   },

   'workout_step' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'wkt_step_name'},
     1 => +{'name' => 'duration_type', 'type_name' => 'wkt_step_duration'},

     2 => +{
       'name' => 'duration_value',

       'switch' => +{
	 '_by' => 'duration_type',
	 'time' => +{'name' => 'duration_time', 'scale' => 1000, 'unit' => 's'},
	 'repetition_time' => +{'name' => 'duration_time', 'scale' => 1000, 'unit' => 's'},
	 'distance' => +{'name' => 'duration_distance', 'scale' => 100, 'unit' => 'm'},
	 'hr_less_than' => +{'name' => 'duration_hr', 'type_name' => 'workout_hr', 'unit' => 'bpm'},
	 'hr_greater_than' => +{'name' => 'duration_hr', 'type_name' => 'workout_hr', 'unit' => 'bpm'},
	 'calories' => +{'name' => 'duration_calories', 'unit' => 'kcal'},
	 'repeat_until_steps_cmplt' => +{'name' => 'duration_step'},
	 'repeat_until_time' => +{'name' => 'duration_step'},
	 'repeat_until_distance' => +{'name' => 'duration_step'},
	 'repeat_until_calories' => +{'name' => 'duration_step'},
	 'repeat_until_hr_less_than' => +{'name' => 'duration_step'},
	 'repeat_until_hr_greater_than' => +{'name' => 'duration_step'},
	 'repeat_until_power_less_than' => +{'name' => 'duration_step'},
	 'repeat_until_power_greater_than' => +{'name' => 'duration_step'},
	 'power_less_than' => +{'name' => 'duration_power', 'unit' => 'watts'},
	 'power_greater_than' => +{'name' => 'duration_power', 'unit' => 'watts'},
       },
     },

     3 => +{'name' => 'target_type', 'type_name' => 'wkt_step_target'},

     4 => +{
       'name' => 'target_value',

       'switch' => +{
	 '_by' => [qw(target_type duration_type)],
 	 'speed' => +{'name' => 'target_speed_zone'}, # not present?
	 'heart_rate' => +{'name' => 'target_hr_zone'},
 	 'cadence' => +{'name' => 'target_cadence_zone'}, # not present?
	 'power' => +{'name' => 'target_power_zone'},
	 'repeat_until_steps_cmplt' => +{'name' => 'repeat_steps'},
	 'repeat_until_time' => +{'name' => 'repeat_time', 'scale' => 1000, 'unit' => 's'},
	 'repeat_until_distance' => +{'name' => 'repeat_distance', 'scale' => 100, 'unit' => 'm'},
	 'repeat_until_calories' => +{'name' => 'repeat_calories', 'unit' => 'kcal'},
	 'repeat_until_hr_less_than' => +{'name' => 'repeat_hr', 'unit' => 'bpm'},
	 'repeat_until_hr_greater_than' => +{'name' => 'repeat_hr', 'unit' => 'bpm'},
	 'repeat_until_power_less_than' => +{'name' => 'repeat_power', 'unit' => 'watts'},
	 'repeat_until_power_greater_than' => +{'name' => 'repeat_power', 'unit' => 'watts'},
       },
     },

     5 => +{
       'name' => 'custom_target_value_low',

       'switch' => +{
	 '_by' => 'target_type',
	 'speed' => +{'name' => 'custom_target_speed_low', 'scale' => 1000, 'unit' => 'm/s'},
	 'heart_rate' => +{'name' => 'custom_target_heart_rate_low', 'type_name' => 'workout_hr', 'unit' => 'bpm'},
	 'cadence' => +{'name' => 'custom_target_cadence_low', 'unit' => 'rpm'},
	 'power' => +{'name' => 'custom_target_power_low', 'type_name' => 'workout_power', 'unit' => 'watts'},
       },
     },

     6 => +{
       'name' => 'custom_target_value_high',

       'switch' => +{
	 '_by' => 'target_type',
	 'speed' => +{'name' => 'custom_target_speed_high', 'scale' => 1000, 'unit' => 'm/s'},
	 'heart_rate' => +{'name' => 'custom_target_heart_rate_high', 'type_name' => 'workout_hr', 'unit' => 'bpm'},
	 'cadence' => +{'name' => 'custom_target_cadence_high', 'unit' => 'rpm'},
	 'power' => +{'name' => 'custom_target_power_high', 'type_name' => 'workout_power', 'unit' => 'watts'},
       },
     },

     7 => +{'name' => 'intensity', 'type_name' => 'intensity'},
   },

   # =================== Schedule file messages ===================
   'schedule' => +{
     0 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     1 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
	 'dynastream_oem' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     2 => +{'name' => 'serial_number'},
     3 => +{'name' => 'time_created', 'type_name' => 'date_time'},
     4 => +{'name' => 'completed'},
     5 => +{'name' => 'type', 'type_name' => 'schedule'},
     6 => +{'name' => 'schedule_time', 'type_name' => 'local_date_time'},
   },

   # =================== Totals file messages ===================
   'totals' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'timer_time', 'unit' => 's'},
     1 => +{'name' => 'distance', 'unit' => 'm'},
     2 => +{'name' => 'calories', 'unit' => 'kcal'},
     3 => +{'name' => 'sport', 'type_name' => 'sport'},
     4 => +{'name' => 'elapsed_time', 'unit' => 's'},
     5 => +{'name' => 'sessions'},
     6 => +{'name' => 'active_time', 'unit' => 's'},
     9 => +{'name' => 'sport_index'},
     10 => +{'name' => 'profile_name'}, # unknown STRING
   },

   # =================== Weight scale file messages ===================
   'weight_scale' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'weight', 'type_name' => 'weight', 'scale' => 100, 'unit' => 'kg'},
     1 => +{'name' => 'percent_fat', 'scale' => 100, 'unit' => '%'},
     2 => +{'name' => 'percent_hydration', 'scale' => 100, 'unit' => '%'},
     3 => +{'name' => 'visceral_fat_mass', 'scale' => 100, 'unit' => 'kg'},
     4 => +{'name' => 'bone_mass', 'scale' => 100, 'unit' => 'kg'},
     5 => +{'name' => 'muscle_mass', 'scale' => 100, 'unit' => 'kg'},
     7 => +{'name' => 'basal_met', 'scale' => 4, 'unit' => 'kcal/day'},
     8 => +{'name' => 'physique_rating'},
     9 => +{'name' => 'active_met', 'scale' => 4, 'unit' => 'kcal/day'},
     10 => +{'name' => 'metabolic_age', 'unit' => 'years'},
     11 => +{'name' => 'visceral_fat_rating'},
     12 => +{'name' => 'user_profile_index', 'type_name' => 'message_index'},
   },

   # =================== Blood pressure file messages ===================
   'blood_pressure' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'systolic_pressure', 'unit' => 'mmHg'},
     1 => +{'name' => 'diastolic_pressure', 'unit' => 'mmHg'},
     2 => +{'name' => 'mean_arterial_pressure', 'unit' => 'mmHg'},
     3 => +{'name' => 'map_3_sample_mean', 'unit' => 'mmHg'},
     4 => +{'name' => 'map_morning_values', 'unit' => 'mmHg'},
     5 => +{'name' => 'map_evening_values', 'unit' => 'mmHg'},
     6 => +{'name' => 'heart_rate', 'unit' => 'bpm'},
     7 => +{'name' => 'heart_rate_type', 'type_name' => 'hr_type'},
     8 => +{'name' => 'status', 'type_name' => 'bp_status'},
     9 => +{'name' => 'user_profile_index', 'type_name' => 'message_index'},
   },

   # =================== Monitoring file messages ===================
   'monitoring_info' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
     1 => +{'name' => 'activity_type', 'type_name' => 'activity_type'},
     3 => +{'name' => 'cycles_to_distance', 'scale' => 5000, 'unit' => 'm/cycle'},
     4 => +{'name' => 'cycles_to_calories', 'scale' => 5000, 'unit' => 'kcal/cycle'},
     5 => +{'name' => 'resting_metabolic_rate', 'unit' => 'kcal / day'},
   },

   'monitoring' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'device_index', 'type_name' => 'device_index'},
     1 => +{'name' => 'calories', 'unit' => 'kcal'},
     2 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},

     3 => +{
       'name' => 'cycles', 'scale' => 2, 'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'activity_type',
	 'walking' => +{'name' => 'total_steps', 'scale' => 1, 'unit' => 'steps'},
	 'running' => +{'name' => 'total_strides', 'scale' => 1, 'unit' => 'strides'},
	 'cycling' => +{'name' => 'total_strokes', 'scale' => 2, 'unit' => 'strokes'},
	 'swimming' => +{'name' => 'total_strokes', 'scale' => 2, 'unit' => 'strokes'},
       },
     },

     4 => +{'name' => 'active_time', 'scale' => 1000, 'unit' => 's'},
     5 => +{'name' => 'activity_type', 'type_name' => 'activity_type'},
     6 => +{'name' => 'activity_subtype', 'type_name' => 'activity_subtype'},
     7 => +{'name' => 'activity_level', 'type_name' => 'activity_level'},
     8 => +{'name' => 'distance_16', 'scale' => 100, 'unit' => 'm'},
     9 => +{'name' => 'cycles_16', 'scale' => 2, 'unit' => 'cycles'},
     10 => +{'name' => 'active_time_16', 'unit' => 's'},
     11 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
     12 => +{'name' => 'temperature', 'scale' => 100, 'unit' => 'deg.C'},
     14 => +{'name' => 'temperature_min', 'scale' => 100, 'unit' => 'deg.C'},
     15 => +{'name' => 'temperature_max', 'scale' => 100, 'unit' => 'deg.C'},
     16 => +{'name' => 'activity_time', 'unit' => 'min'},
     19 => +{'name' => 'active_calories', 'unit' => 'kcal'},
     24 => +{'name' => 'current_activity_type_intensity'},
     25 => +{'name' => 'timestamp_min_8', 'unit' => 'min'},
     26 => +{'name' => 'timestamp_16', 'unit' => 's'},
     27 => +{'name' => 'heart_rate', 'unit' => 'bpm'},
     28 => +{'name' => 'intensity', 'scale' => 10},
     29 => +{'name' => 'duration_min', 'unit' => 'min'},
     30 => +{'name' => 'duration', 'unit' => 's'},
     31 => +{'name' => 'ascent', 'scale' => 1000, 'unit' => 'm'},
     32 => +{'name' => 'descent', 'scale' => 1000, 'unit' => 'm'},
     33 => +{'name' => 'moderate_activity_minutes', 'unit' => 'min'},
     34 => +{'name' => 'vigorous_activity_minutes', 'unit' => 'min'},
   },

   'hr' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'fractional_timestamp', 'scale' => 32768, 'unit' => 's'},
     1 => +{'name' => 'time256', 'scale' => 256, 'unit' => 's'},
     6 => +{'name' => 'filtered_bpm', 'unit' => 'bpm'},
     9 => +{'name' => 'event_timestamp', 'scale' => 1024, 'unit' => 's'},
     10 => +{'name' => 'event_timestamp_12', 'scale' => 1024, 'unit' => 's'},
   },

   # =================== Other messages ===================
   'memo_glob' => +{
     250 => +{'name' => 'part_index'},
     0 => +{'name' => 'memo'},
     1 => +{'name' => 'message_number'},
     2 => +{'name' => 'message_index', 'type_name' => 'message_index'},
   },

   'ant_channel_id' => +{
     0 => +{'name' => 'channel_number'},
     1 => +{'name' => 'device_type'},
     2 => +{'name' => 'device_number'},
     3 => +{'name' => 'transmission_type'},
     4 => +{'name' => 'device_index', 'type_name' => 'device_index'},
   },

   'ant_rx' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'fractional_timestamp', 'scale' => 32768, 'unit' => 's'},
     1 => +{'name' => 'mesg_id'},
     2 => +{'name' => 'mesg_data'},
     3 => +{'name' => 'channel_number'},
     4 => +{'name' => 'data'},
   },

   'ant_tx' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'fractional_timestamp', 'scale' => 32768, 'unit' => 's'},
     1 => +{'name' => 'mesg_id'},
     2 => +{'name' => 'mesg_data'},
     3 => +{'name' => 'channel_number'},
     4 => +{'name' => 'data'},
   },

   'exd_screen_configuration' => +{
     0 => +{'name' => 'screen_index'},
     1 => +{'name' => 'field_count'},
     2 => +{'name' => 'layout', 'type_name' => 'exd_layout'},
     3 => +{'name' => 'screen_enabled'},
   },

   'exd_data_field_configuration' => +{
     0 => +{'name' => 'screen_index'},
     1 => +{'name' => 'concept_field'},
     2 => +{'name' => 'field_id'},
     3 => +{'name' => 'concept_count'},
     4 => +{'name' => 'display_type', 'type_name' => 'exd_display_type'},
     5 => +{'name' => 'title'},
   },

   'exd_data_concept_configuration' => +{
     0 => +{'name' => 'screen_index'},
     1 => +{'name' => 'concept_field'},
     2 => +{'name' => 'field_id'},
     3 => +{'name' => 'concept_index'},
     4 => +{'name' => 'data_page'},
     5 => +{'name' => 'concept_key'},
     6 => +{'name' => 'scaling'},
     7 => +{'name' => 'unknown7'}, # unknown UINT8
     8 => +{'name' => 'data_units', 'type_name' => 'exd_data_units'},
     9 => +{'name' => 'qualifier', 'type_name' => 'exd_qualifiers'},
     10 => +{'name' => 'descriptor', 'type_name' => 'exd_descriptors'},
     11 => +{'name' => 'is_signed'},
   },

   'field_description' => +{
     0 => +{'name' => 'developer_data_index'},
     1 => +{'name' => 'field_definition_number'},
     2 => +{'name' => 'fit_base_type_id', 'type_name' => 'fit_base_type'},
     3 => +{'name' => 'field_name'},
     4 => +{'name' => 'array'},
     5 => +{'name' => 'components'},
     6 => +{'name' => 'scale'},
     7 => +{'name' => 'offset'},
     8 => +{'name' => 'units'},
     9 => +{'name' => 'bits'},
     10 => +{'name' => 'accumulate'},
     13 => +{'name' => 'fit_base_unit_id', 'type_name' => 'fit_base_unit'},
     14 => +{'name' => 'native_mesg_num', 'type_name' => 'mesg_num'},
     15 => +{'name' => 'native_field_num'},
   },

   'developer_data_id' => +{
     0 => +{'name' => 'developer_id'},
     1 => +{'name' => 'application_id'},
     2 => +{'name' => 'manufacturer_id', 'type_name' => 'manufacturer'},
     3 => +{'name' => 'developer_data_index'},
     4 => +{'name' => 'application_version'},
   },

   # =================== Undocumented messages ===================
   'source' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     # device_index in device_info
     0 => +{'name' => 'unknown0', 'type_name' => 'device_index'}, # unknown UINT8
     1 => +{'name' => 'unknown1', 'type_name' => 'device_index'}, # unknown UINT8
     2 => +{'name' => 'unknown2', 'type_name' => 'device_index'}, # unknown UINT8
     3 => +{'name' => 'unknown3', 'type_name' => 'device_index'}, # unknown UINT8
     4 => +{'name' => 'unknown4', 'type_name' => 'device_index'}, # unknown UINT8
     5 => +{'name' => 'unknown5'}, # unknown ENUM
     6 => +{'name' => 'unknown6'}, # unknown UINT8
     7 => +{'name' => 'unknown7'}, # unknown UINT8
     8 => +{'name' => 'unknown8'}, # unknown UINT8
     9 => +{'name' => 'unknown9'}, # unknown UINT8
   },

   'location' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'name'}, # unknown STRING
     1 => +{'name' => 'position_lat', 'unit' => 'semicircles'}, # unknown SINT32
     2 => +{'name' => 'position_long', 'unit' => 'semicircles'}, # unknown SINT32
     3 => +{'name' => 'unknown3'}, # unknown UINT16 (elevation?)
     4 => +{'name' => 'unknown4'}, # unknown UINT16
     5 => +{'name' => 'unknown5'}, # unknown UINT16
     6 => +{'name' => 'unknown6'}, # unknown STRING
   },

   'battery' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'unknown0'}, # unknown UINT16 (voltage with scale?)
     1 => +{'name' => 'unknown1'}, # unknown SINT16
     2 => +{'name' => 'charge_level', 'unit' => '%'}, # unknown UINT8
     3 => +{'name' => 'temperature', 'unit' => 'deg.C'}, # unknown SINT8
   },

   'sensor' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'unknown0'}, # unknown UINT32Z
     1 => +{'name' => 'unknown1'}, # unknown UINT8
     2 => +{'name' => 'sensor_id'}, # unknown STRING
     3 => +{'name' => 'unknown3'}, # unknown ENUM
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'unknown5'}, # unknown ENUM
     6 => +{'name' => 'unknown6'}, # unknown ENUM
     7 => +{'name' => 'unknown7'}, # unknown ENUM
     8 => +{'name' => 'unknown8'}, # unknown ENUM
     9 => +{'name' => 'unknown9'}, # unknown UINT8
     10 => +{'name' => 'wheel_size', 'unit' => 'mm'}, # unknown UINT16
     11 => +{'name' => 'unknown11'}, # unknown UINT16
     12 => +{'name' => 'unknown12'}, # unknown UINT8
     13 => +{'name' => 'unknown13'}, # unknown UINT32
     14 => +{'name' => 'unknown14'}, # unknown UINT8
     15 => +{'name' => 'unknown15'}, # unknown UINT8
     16 => +{'name' => 'unknown16'}, # unknown UINT8
     17 => +{'name' => 'unknown17'}, # unknown UINT8Z
     18 => +{'name' => 'unknown18'}, # unknown UINT8Z (array[4])
     19 => +{'name' => 'unknown19'}, # unknown UINT8Z
     20 => +{'name' => 'unknown20'}, # unknown UINT8Z (array[12])
     21 => +{'name' => 'unknown21'}, # unknown UINT16
     25 => +{'name' => 'unknown25'}, # unknown UINT16
     26 => +{'name' => 'unknown26'}, # unknown UINT16
     27 => +{'name' => 'unknown27'}, # unknown UINT8
     28 => +{'name' => 'unknown28'}, # unknown UINT8 (array[4])
     29 => +{'name' => 'unknown29'}, # unknown UINT8 (array[4])
     30 => +{'name' => 'unknown30'}, # unknown UINT8 (array[4])
     31 => +{'name' => 'unknown31'}, # unknown UINT8
     32 => +{'name' => 'unknown32'}, # unknown UINT16
     33 => +{'name' => 'unknown33'}, # unknown UINT16
     34 => +{'name' => 'unknown34'}, # unknown UINT16
     35 => +{'name' => 'unknown35'}, # unknown UINT16
     36 => +{'name' => 'unknown36'}, # unknown ENUM
     37 => +{'name' => 'unknown37'}, # unknown ENUM (array[7])
     38 => +{'name' => 'unknown38'}, # unknown ENUM (array[7])
     39 => +{'name' => 'unknown39'}, # unknown ENUM (array[7])
     40 => +{'name' => 'unknown40'}, # unknown UINT16Z
     41 => +{'name' => 'unknown41'}, # unknown UINT8 (array[7])
     42 => +{'name' => 'unknown42'}, # unknown ENUM
     43 => +{'name' => 'unknown43'}, # unknown ENUM
     44 => +{'name' => 'unknown44'}, # unknown UINT8Z
     47 => +{'name' => 'unknown47'}, # unknown ENUM
     48 => +{'name' => 'unknown48'}, # unknown ENUM
   },

   );

$mesg_name_vs_num = $named_type{mesg_num};

%msgtype_by_num =
  (

   # =================== Unknown messages ===================
   13 => +{
     '_number' => 13,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown UINT16
     3 => +{'name' => 'unknown3'}, # unknown ENUM
     4 => +{'name' => 'unknown4'}, # unknown UINT32
     5 => +{'name' => 'unknown5'}, # unknown SINT32
     6 => +{'name' => 'unknown6'}, # unknown SINT32
     7 => +{'name' => 'unknown7'}, # unknown ENUM
     8 => +{'name' => 'unknown8'}, # unknown UINT16
     9 => +{'name' => 'unknown9'}, # unknown ENUM
     10 => +{'name' => 'unknown10'}, # unknown UINT16
     11 => +{'name' => 'unknown11'}, # unknown UINT8
     12 => +{'name' => 'unknown12'}, # unknown ENUM
     13 => +{'name' => 'unknown13'}, # unknown ENUM
     14 => +{'name' => 'unknown14'}, # unknown ENUM
     15 => +{'name' => 'unknown15'}, # unknown ENUM
     16 => +{'name' => 'unknown16'}, # unknown ENUM
     17 => +{'name' => 'unknown17'}, # unknown ENUM
     18 => +{'name' => 'unknown18'}, # unknown ENUM
     19 => +{'name' => 'unknown19'}, # unknown UINT16
     27 => +{'name' => 'unknown27'}, # unknown ENUM
     30 => +{'name' => 'unknown30'}, # unknown ENUM
     31 => +{'name' => 'unknown31'}, # unknown UINT32
     32 => +{'name' => 'unknown32'}, # unknown UINT16
     33 => +{'name' => 'unknown33'}, # unknown UINT32
     34 => +{'name' => 'unknown34'}, # unknown ENUM
     50 => +{'name' => 'unknown50'}, # unknown ENUM
   },

   14 => +{
     '_number' => 14,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     3 => +{'name' => 'unknown3'}, # unknown UINT8
     4 => +{'name' => 'unknown4'}, # unknown UINT8 (array[10])
     5 => +{'name' => 'unknown5'}, # unknown ENUM (array[10])
     6 => +{'name' => 'unknown6'}, # unknown STRING
     7 => +{'name' => 'unknown7'}, # unknown UINT16 (array[10])
   },

   16 => +{
     '_number' => 16,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown UINT32
     3 => +{'name' => 'unknown3'}, # unknown ENUM
   },

   17 => +{
     '_number' => 17,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown ENUM
     3 => +{'name' => 'unknown3'}, # unknown UINT16
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'unknown5'}, # unknown UINT16
   },

   70 => +{
     '_number' => 70,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'unknown0'}, # unknown ENUM
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown ENUM
     3 => +{'name' => 'unknown3'}, # unknown ENUM
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'unknown5'}, # unknown ENUM
     6 => +{'name' => 'unknown6'}, # unknown ENUM
     7 => +{'name' => 'unknown7'}, # unknown ENUM
     8 => +{'name' => 'unknown8'}, # unknown ENUM
     9 => +{'name' => 'unknown9'}, # unknown ENUM
     10 => +{'name' => 'unknown10'}, # unknown ENUM
     11 => +{'name' => 'unknown11'}, # unknown ENUM
     12 => +{'name' => 'unknown12'}, # unknown ENUM
     13 => +{'name' => 'unknown13'}, # unknown ENUM
     14 => +{'name' => 'unknown14'}, # unknown ENUM
     15 => +{'name' => 'unknown15'}, # unknown ENUM
   },

   71 => +{
     '_number' => 71,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'unknown0'}, # unknown ENUM
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown ENUM
     3 => +{'name' => 'unknown3'}, # unknown UINT16
     4 => +{'name' => 'unknown4'}, # unknown ENUM
   },

   79 => +{
     '_number' => 79,
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'unknown0'}, # unknown UINT16
     1 => +{'name' => 'unknown1'}, # unknown UINT8
     2 => +{'name' => 'unknown2'}, # unknown UINT8
     3 => +{'name' => 'unknown3'}, # unknown UINT16
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'unknown5'}, # unknown ENUM
     6 => +{'name' => 'unknown6'}, # unknown UINT8
     7 => +{'name' => 'unknown7'}, # unknown SINT8
     8 => +{'name' => 'unknown8'}, # unknown UINT16
     9 => +{'name' => 'unknown9'}, # unknown UINT16
     10 => +{'name' => 'unknown10'}, # unknown UINT8
     11 => +{'name' => 'unknown11'}, # unknown UINT16
     12 => +{'name' => 'unknown12'}, # unknown UINT16
     13 => +{'name' => 'unknown13'}, # unknown UINT16
     14 => +{'name' => 'unknown14'}, # unknown UINT8
   },

   113 => +{
     '_number' => 113,
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'unknown0'}, # unknown UINT16
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown UINT32
     3 => +{'name' => 'unknown3'}, # unknown UINT32
     4 => +{'name' => 'unknown4'}, # unknown UINT32
     5 => +{'name' => 'unknown5'}, # unknown ENUM
   },

   114 => +{
     '_number' => 114,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'unknown0'}, # unknown UINT16
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown UINT32
     3 => +{'name' => 'unknown3'}, # unknown UINT32
     4 => +{'name' => 'unknown4'}, # unknown UINT32
     5 => +{'name' => 'unknown5'}, # unknown UINT32
     6 => +{'name' => 'unknown6'}, # unknown UINT32Z
     7 => +{'name' => 'unknown7'}, # unknown UINT32
   },

   139 => +{
     '_number' => 139,
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'unknown0'}, # unknown ENUM
     1 => +{'name' => 'unknown1'}, # unknown UINT16Z
     3 => +{'name' => 'unknown3'}, # unknown UINT8Z
     4 => +{'name' => 'unknown4'}, # unknown ENUM
     5 => +{'name' => 'unknown5'}, # unknown UINT16
   },

   140 => +{
     '_number' => 140,
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'unknown0'}, # unknown UINT8
     1 => +{'name' => 'unknown1'}, # unknown UINT8
     2 => +{'name' => 'unknown2'}, # unknown SINT32
     3 => +{'name' => 'unknown3'}, # unknown SINT32
     4 => +{'name' => 'unknown4'}, # unknown UINT8
     5 => +{'name' => 'unknown5'}, # unknown SINT32
     6 => +{'name' => 'unknown6'}, # unknown SINT32
     7 => +{'name' => 'unknown7'}, # unknown SINT32
     8 => +{'name' => 'unknown8'}, # unknown UINT8
     9 => +{'name' => 'unknown9'}, # unknown UINT16
     10 => +{'name' => 'unknown10'}, # unknown UINT16
     11 => +{'name' => 'unknown11'}, # unknown ENUM
     12 => +{'name' => 'unknown12'}, # unknown ENUM
     13 => +{'name' => 'unknown13'}, # unknown UINT8
     14 => +{'name' => 'unknown14'}, # unknown UINT16
     15 => +{'name' => 'unknown15'}, # unknown UINT16
     16 => +{'name' => 'unknown16'}, # unknown UINT16
     17 => +{'name' => 'unknown17'}, # unknown SINT8
     18 => +{'name' => 'unknown18'}, # unknown UINT8
     19 => +{'name' => 'unknown19'}, # unknown UINT8
   },

   203 => +{
     '_number' => 203,
     0 => +{'name' => 'unknown0'}, # unknown ENUM
     1 => +{'name' => 'unknown1'}, # unknown ENUM
     2 => +{'name' => 'unknown2'}, # unknown ENUM
   },

   );

my $msgname;

foreach $msgname (keys %msgtype_by_name) {
  my $msgtype = $msgtype_by_name{$msgname};

  $msgtype->{_name} = $msgname;
  $msgtype->{_number} = $mesg_name_vs_num->{$msgname};
  $msgtype_by_num{$msgtype->{_number}} = $msgtype;

  my $fldnum;

  foreach $fldnum (grep {/^\d+$/} keys %$msgtype) {
    my $flddesc = $msgtype->{$fldnum};

    $flddesc->{number} = $fldnum;
    $msgtype->{$flddesc->{name}} = $flddesc;
  }
}

sub message_name {
  my ($self, $mspec) = @_;
  my $msgtype = $mspec =~ /^\d+$/ ? $msgtype_by_num{$mspec} : $msgtype_by_name{$mspec};

  if (ref $msgtype eq 'HASH') {
    $msgtype->{_name};
  }
  else {
    undef;
  }
}

sub message_number {
  my ($self, $mspec) = @_;
  my $msgtype = $mspec =~ /^\d+$/ ? $msgtype_by_num{$mspec} : $msgtype_by_name{$mspec};

  if (ref $msgtype eq 'HASH') {
    $msgtype->{_number};
  }
  else {
    undef;
  }
}

sub field_name {
  my ($self, $mspec, $fspec) = @_;
  my $msgtype = $mspec =~ /^\d+$/ ? $msgtype_by_num{$mspec} : $msgtype_by_name{$mspec};

  if (ref $msgtype eq 'HASH') {
    my $flddesc = $msgtype->{$fspec};

    ref $flddesc eq 'HASH'
      and return $flddesc->{name};
  }

  undef;
}

sub field_number {
  my ($self, $mspec, $fspec) = @_;
  my $msgtype = $mspec =~ /^\d+$/ ? $msgtype_by_num{$mspec} : $msgtype_by_name{$mspec};

  if (ref $msgtype eq 'HASH') {
    my $flddesc = $msgtype->{$fspec};

    ref $flddesc eq 'HASH'
      and return $flddesc->{number};
  }

  undef;
}

sub data_message_descriptor {
  my $self = shift;

  if (@_) {
    $self->{data_message_descriptor} = $_[0];
  }
  else {
    $self->{data_message_descriptor} = [] if ref $self->{data_message_descriptor} ne 'ARRAY';
    $self->{data_message_descriptor};
  }
}

sub data_message_callback {
  $_[0]->{data_message_callback};
}

$msgnum_anon = $invalid[FIT_UINT16];
$msgname_anon = '';

sub data_message_callback_by_num {
  my $self = shift;
  my $num = shift;
  my $cbmap = $self->data_message_callback;
  my ($msgtype, $name);

  if ($num == $msgnum_anon) {
    if (@_) {
      if (ref $_[0] eq 'CODE') {
	$cbmap->{$msgname_anon} = $cbmap->{$msgnum_anon} = [@_];
      }
      else {
	$self->error('not a CODE');
      }
    }
    else {
      my %res;

      foreach $num (keys %msgtype_by_num) {
	my $cb = $cbmap->{$num};

	ref $cb eq 'ARRAY' and $res{$num} = [@$cb];
      }

      \%res;
    }
  }
  elsif (!defined($msgtype = $msgtype_by_num{$num})) {
    $self->error("$num is not a message type number");
  }
  elsif (@_) {
    if (ref $_[0] eq 'CODE') {
      $cbmap->{$num} = [@_];
      $msgtype->{_name} ne '' and $cbmap->{$msgtype->{_name}} = $cbmap->{$num};
      $cbmap->{$num};
    }
    else {
      $self->error('not a CODE');
    }
  }
  else {
    my $cb = $cbmap->{$num};

    ref $cb eq 'ARRAY' ? [@$cb] : [];
  }
}

sub data_message_callback_by_name {
  my $self = shift;
  my $name = shift;
  my $cbmap = $self->data_message_callback;
  my $msgtype;

  if ($name eq $msgname_anon) {
    if (@_) {
      if (ref $_[0] eq 'CODE') {
	$cbmap->{$msgname_anon} = $cbmap->{$msgnum_anon} = [@_];
      }
      else {
	$self->error('not a CODE');
      }
    }
    else {
      my %res;

      foreach $name (keys %msgtype_by_name) {
	my $cb = $cbmap->{$name};

	ref $cb eq 'ARRAY' and $res{$name} = [@$cb];
      }

      \%res;
    }
  }
  elsif (!defined($msgtype = $msgtype_by_name{$name})) {
    $self->error("$name is not a message type name");
  }
  elsif (@_) {
    if (ref $_[0] eq 'CODE') {
      $cbmap->{$msgtype->{_number}} = $cbmap->{$name} = [@_];
    }
    else {
      $self->error('not a CODE');
    }
  }
  else {
    my $cb = $cbmap->{$name};

    ref $cb eq 'ARRAY' ? [@$cb] : [];
  }
}

sub undocumented_field_name {
  my ($self, $index, $size, $type, $i_string) = @_;

# 'xxx' . $i_string . '_' . $index . '_' . $size . '_' . $type;
  'xxx' . $index;
}

sub syscallback_devdata_id {
  my ($self, $desc, $v) = @_;
  my ($i_id, $T_id, $c_id, $i_index) = @$desc{qw(i_application_id T_application_id c_application_id i_developer_data_index)};
  my $emsg;

  if (!defined $i_id) {
    $emsg = "no application_id";
  }
  elsif ($T_id != FIT_UINT8 && $T_id != FIT_BYTE) {
    $emsg = "base type of application_id is $type_name[$T_id] ($T_id)";
  }
  elsif (!defined $i_index) {
    $emsg = "no developer_data_index";
  }

  if ($emsg ne '') {
    $self->error("Broken developer data id message ($emsg)");
    return undef;
  }

  my $devdata_by_index = $self->{devdata_by_index};

  ref $devdata_by_index eq 'HASH' or $devdata_by_index = $self->{devdata_by_index} = +{};

  my $devdata_by_id = $self->{devdata_by_id};

  ref $devdata_by_id eq 'HASH' or $devdata_by_id = $self->{devdata_by_id} = +{};

  my $id;

  if ($T_id == FIT_UINT8) {
    $id = pack('C*', @$v[$i_id .. ($i_id + $c_id - 1)]);
  }
  else {
    $id = $v->[$i_id];
  }

  my %devdata = (id => $id, index => $v->[$i_index]);

  $devdata_by_id->{$devdata{id}} = $devdata_by_index->{$devdata{index}} = \%devdata;
}

sub syscallback_devdata_field_desc {
  my ($self, $desc, $v) = @_;

  my ($i_index, $I_index, $i_field_num, $I_field_num,
      $i_base_type_id, $T_base_type_id, $I_base_type_id,
      $i_field_name, $T_field_name, $c_field_name)
    = @$desc{qw(i_developer_data_index I_developer_data_index i_field_definition_number I_field_definition_number
		i_fit_base_type_id T_fit_base_type_id I_fit_base_type_id
		i_field_name T_field_name c_field_name)};

  my $emsg;

  if (!defined $i_index) {
    $emsg = 'no developer_data_index';
  }
  elsif (!defined $i_field_num) {
    $emsg = 'no field_num';
  }
  elsif (!defined $i_base_type_id) {
    $emsg = 'no base_type_id';
  }
  elsif ($T_base_type_id != FIT_UINT8) {
    $emsg = "base type of base_type_id is $type_name[$T_base_type_id] ($T_base_type_id)";
  }
  elsif (!defined $i_field_name) {
    $emsg = 'no field_name';
  }
  elsif ($T_field_name != FIT_STRING || $c_field_name <= 0) {
    $emsg = "field_name is not a non-empty string";
  }

  if ($emsg ne '') {
    $self->error("broken field description message ($emsg)");
    return undef;
  }

  my $base_type = $v->[$i_base_type_id];

  if ($base_type == $I_base_type) {
    $self->error("invalid base type ($base_type)");
    return undef;
  }

  if ($base_type < 0) {
    $self->error("unknown base type ($base_type)");
    return undef;
  }

  $base_type &= $deffld_mask_type;

  unless ($base_type <= FIT_BASE_TYPE_MAX) {
    $self->error("unknown base type ($base_type)");
    return undef;
  }

  my $devdata_by_index = $self->{devdata_by_index};

  unless (ref $devdata_by_index eq 'HASH') {
    $self->error('no developer data id message before a field description message');
    return undef;
  }

  my $index = $v->[$i_index];

  if ($index == $I_index) {
    $self->error("invalid developer data index ($index)");
    return undef;
  }

  my $num = $v->[$i_field_num];

  if ($num == $I_field_num) {
    $self->error("invalid field definition number ($num)");
    return undef;
  }

  my $devdata = $devdata_by_index->{$index};

  unless (ref $devdata eq 'HASH') {
    $self->error("No developer data id message with the index $index before a field description message");
    return undef;
  }

  my $field_desc_by_num = $devdata->{field_desc_by_num};

  ref $field_desc_by_num eq 'HASH' or $field_desc_by_num = $devdata->{field_desc_by_num} = +{};

  my $field_desc_by_name = $devdata->{field_desc_by_name};

  ref $field_desc_by_name eq 'HASH' or $field_desc_by_name = $devdata->{field_desc_by_name} = +{};

  my $o_name = $self->string_value($v, $i_field_name, $c_field_name);

  if ($o_name eq '') {
    $self->error("name of field $index/$num is null");
    return undef;
  }

  my $name = $o_name;

  $name =~ s/\s+/_/g;
  $name =~ s/\W/sprintf('_%02x_', ord($&))/ge;

  my %fdesc =
    (
     '_index' => $index,
     '_num' => $num,
     '_name' => $name,
     'field_name' => $o_name,
     '_type' => $base_type,
     );

  my $i_aname;

  foreach $i_aname (grep {/^i_/} keys %$desc) {
    if ($i_aname !~ /^i_(developer_data_index|field_definition_number|fit_base_type_id|field_name)$/) {
      my $i = $desc->{$i_aname};
      my $aname = $i_aname;

      $aname =~ s/^i_//;

      my $I_aname = 'I_' . $aname;
      my $T_aname = 'T_' . $aname;
      my $c_aname = 'c_' . $aname;

      if ($desc->{$T_aname} == FIT_STRING) {
	$fdesc{$aname} = $self->string_value($v, $i, $desc->{$c_aname});
      }
      elsif ($v->[$i] != $desc->{$I_aname}) {
	$fdesc{$aname} = $v->[$i];
      }
    }
  }

  $field_desc_by_num->{$num} = \%fdesc;
  $field_desc_by_name->{$name} = \%fdesc;
}

sub add_endian_converter {
  my ($self, $endian, $type, $c, $i_string, $cvt) = @_;

  if ($endian != $my_endian && $size[$type] > 1) {
    my ($p, $unp, $n);

    if ($size[$type] == 2) {
      ($p, $unp) = (qw(n v));
    }
    elsif ($size[$type] == 4) {
      ($p, $unp) = (qw(N V));
    }
    else {
      ($p, $unp, $n) = (qw(N V), 2);
    }

    push @$cvt, $p . $n, $unp . $n, $i_string, $size[$type], $c;
    1;
  }
  else {
    0;
  }
}

sub fetch_definition_message {
  my $self = shift;

  $self->fill_buffer($defmsg_min_length) || return undef;

  my $buffer = $self->buffer;
  my $i = $self->offset;
  my ($rechd, $reserved, $endian, $msgnum, $nfields) = unpack($defmsg_min_template, substr($$buffer, $i, $defmsg_min_length));

  $endian = $endian ? 1 : 0;
  $self->offset($i + $defmsg_min_length);

  my $len = $nfields * $deffld_length;

  $self->fill_buffer($len) || return undef;
  $i = $self->offset;
  $msgnum = unpack('n', pack('v', $msgnum)) if $endian != $my_endian;

  my $msgtype = $msgtype_by_num{$msgnum};
  my $cbmap = $self->data_message_callback;
  my $e = $i + $len;
  my ($cb, %desc, $i_array, $i_array_t, $i_string, @cvt, @pi);

  $desc{local_message_type} = $rechd & $rechd_mask_local_message_type;
  $desc{message_number} = $msgnum;
  $desc{message_name} = $msgtype->{_name} if ref $msgtype eq 'HASH' && exists $msgtype->{_name};
  $cb = $cbmap->{$msgnum} if ref $cbmap->{$msgnum} eq 'ARRAY';
  $cb = $cbmap->{$msgnum_anon} if ref $cb ne 'ARRAY';
  $desc{callback} = $cb if ref $cb eq 'ARRAY';
  $desc{endian} = $endian;
  $desc{template} = 'C';
  $self->data_message_descriptor->[$desc{local_message_type}] = \%desc;

  for ($i_array = $i_array_t = $i_string = 1 ; $i + $deffld_length <= $e ; $i += $deffld_length) {
    my ($index, $size, $type) = unpack($deffld_template, substr($$buffer, $i, $deffld_length));
    my ($name, $tname, %attr, );

    if (ref $msgtype eq 'HASH') {
      my $fldtype = $msgtype->{$index};

      if (ref $fldtype eq 'HASH') {
	%attr = %$fldtype;
	($name, $tname) = @attr{qw(name type_name)};
	delete $attr{name};
	delete $attr{type_name};
      }
    }

    $name = $self->undocumented_field_name($index, $size, $type, $i_string) if !defined $name;
    $desc{$index} = $name;
    $type &= $deffld_mask_type;

    my $c = int($size / $size[$type] + 0.5);

    $desc{'i_' . $name} = $i_array;
    $desc{'o_' . $name} = $i_string;
    $desc{'c_' . $name} = $c;
    $desc{'s_' . $name} = $size[$type];
    $desc{'a_' . $name} = \%attr if %attr;
    $desc{'t_' . $name} = $tname if defined $tname;
    $desc{'T_' . $name} = $type;
    $desc{'N_' . $name} = $index;
    $desc{'I_' . $name} = $invalid[$type];

    $self->add_endian_converter($endian, $type, $c, $i_string, \@cvt);

    $i_array += $c;
    $i_string += $size;
    $desc{template} .= ' ' . $template[$type];

    if ($packfactor[$type] > 1) {
      push @pi, $i_array_t, $c, $i_array;
      $c *= $packfactor[$type];
    }

    $desc{template} .= $c if $c > 1;
    $i_array_t += $c;
  }

  $desc{devdata_nfields} = 0;

  if ($rechd & $rechd_mask_devdata_message) {
    $self->offset($e);
    $self->fill_buffer($devdata_min_length) || return undef;
    $i = $self->offset;
    ($nfields) = unpack($devdata_min_template, substr($$buffer, $i, $devdata_min_length));
    $self->offset($i + $devdata_min_length);
    $len = $nfields * $devdata_deffld_length;
    $self->fill_buffer($len) || return undef;

    my $devdata_by_index = $self->{devdata_by_index};
    my @emsg;

    if (ref $devdata_by_index ne 'HASH') {
      push @emsg, 'No developer data id';
      $devdata_by_index = +{};
    }

    for ($i = $self->offset, $e = $i + $len ; $i + $devdata_deffld_length <= $e ; $i += $devdata_deffld_length) {
      my ($fnum, $size, $index) = unpack($devdata_deffld_template, substr($$buffer, $i, $devdata_deffld_length));
      my $devdata = $devdata_by_index->{$index};
      my ($fdesc, $name, $type, %attr);

      if (ref $devdata eq 'HASH') {
	my $fdesc_by_num = $devdata->{field_desc_by_num};

	if (ref $fdesc_by_num eq 'HASH') {
	  $fdesc = $fdesc_by_num->{$fnum};
	}
	else {
	  push @emsg, "No field description message for developer data with index $index";
	}
      }
      else {
	push @emsg, "No developer data id with index $index";
      }

      if (ref $fdesc eq 'HASH') {
	%attr = %$fdesc;
	($type, $name) = @attr{qw(_type _name)};
      }
      else {
	push @emsg, "No field with number $fnum for developer data with index $index";
	$type = FIT_UINT8;
      }

      $name = $self->undocumented_field_name($fnum, $size, $type, $i_string) if !defined $name;
      $name = "${index}_$name";

      my $c = int($size / $size[$type] + 0.5);

      $desc{'i_' . $name} = $i_array;
      $desc{'o_' . $name} = $i_string;
      $desc{'c_' . $name} = $c;
      $desc{'s_' . $name} = $size[$type];
      $desc{'a_' . $name} = \%attr if %attr;
      $desc{'T_' . $name} = $type;
      $desc{'N_' . $name} = $fnum;
      $desc{'I_' . $name} = $invalid[$type];

      $self->add_endian_converter($endian, $type, $c, $i_string, \@cvt);

      $i_array += $c;
      $i_string += $size;
      $desc{template} .= ' ' . $template[$type];

      if ($packfactor[$type] > 1) {
	push @pi, $type, $i_array_t, $c, $i_array;
	$c *= $packfactor[$type];
      }

      $desc{template} .= $c if $c > 1;
      $i_array_t += $c;
    }

    $desc{devdata_nfields} = $nfields;
    $self->error(join(' / ', @emsg)) if (@emsg);
  }

  $desc{endian_converter} = \@cvt if @cvt;
  $desc{packfilter_index} = \@pi if @pi;
  $desc{message_length} = $i_string;
  $desc{array_length} = $i_array;
  $self->offset($e);
  1;
}

sub cat_definition_message {
  my ($self, $desc, $p) = @_;

  if (!defined $p) {
    my $bin = '';

    $p = \$bin;
  }

  my @i_name = sort {$desc->{$a} <=> $desc->{$b}} grep {/^i_[A-Za-z]/} keys %$desc;
  my @devdata_i_name = sort {$desc->{$a} <=> $desc->{$b}} grep {/^i_\d+_/} keys %$desc;
  my $mask = @devdata_i_name ? $rechd_mask_devdata_message : 0;
  my ($endian, $msgnum) = @{$desc}{qw(endian message_number)};

  $msgnum = unpack('n', pack('v', $msgnum)) if $endian != $my_endian;
  $$p .= pack($defmsg_min_template, $desc->{local_message_type} | $rechd_mask_definition_message | $mask, 0, $endian, $msgnum, $#i_name + 1);

  while (@i_name) {
    my $name = shift @i_name;

    $name =~ s/^i_//;

    my $size = $desc->{'s_' . $name};

    $$p .= pack($deffld_template, $desc->{'N_' . $name}, $desc->{'c_' . $name} * $size, $desc->{'T_' . $name} | ($size > 1 ? $deffld_mask_endian_p : 0));
  }

  if (@devdata_i_name) {
    $$p .= pack($devdata_min_template, $#devdata_i_name + 1);

    while (@devdata_i_name) {
      my $name = shift @devdata_i_name;

      $name =~ s/^i_//;

      my $size = $desc->{'s_' . $name};

      $$p .= pack($devdata_deffld_template, $desc->{'N_' . $name}, $desc->{'c_' . $name} * $size, $name =~ /^(\d+)_/);
    }
  }

  $p;
}

sub endian_convert {
  my ($self, $cvt, $buffer, $i) = @_;
  my $j;

  for ($j = 4 ; $j < @$cvt ; $j += 5) {
    my ($b, $size, $c) = @$cvt[$j - 2, $j - 1, $j];

    for ($b += $i ; $c > 0 ; $b += $size, --$c) {
      my @v = unpack($cvt->[$j - 3], substr($$buffer, $b, $size));
      my ($k, $l);

      for ($k = 0, $l = $#v ; $k < $l ; ++$k, --$l) {
	@v[$k, $l] = @v[$l, $k];
      }

      substr($$buffer, $b, $size) = pack($cvt->[$j - 4], @v);
    }
  }
}

sub last_timestamp {
  my $self = shift;

  if (@_) {
    $self->{last_timestamp} = $_[0];
  }
  else {
    $self->{last_timestamp};
  }
}

sub fetch_data_message {
  my ($self, $desc) = @_;

  $self->fill_buffer($desc->{message_length}) || return undef;
  $self->endian_convert($desc->{endian_converter}, $self->buffer, $self->offset) if ref $desc->{endian_converter} eq 'ARRAY';

  my $buffer = $self->buffer;
  my $i = $self->offset;
  # unpack('f'/'d', ...) unpacks to NaN
  my @v = unpack($desc->{template}, substr($$buffer, $i, $desc->{message_length}));

  if (ref $desc->{packfilter_index} eq 'ARRAY') {
    my $piv = $desc->{packfilter_index};
    my ($i, $j);
    my @v_t = @v;

    @v = ($v_t[0]);

    for ($i = 1, $j = 3 ; $j < @$piv ; $j += 4) {
      my ($type, $i_array_t, $c, $i_array) = @$piv[($j - 3) .. $j];
      my $delta = $packfactor[$type];

      $i < $i_array_t and push @v, @v_t[$i .. ($i_array_t - 1)];
      $i = $i_array_t + $c * $delta;

      for (; $i_array_t < $i ; $i_array_t += $delta) {
	push @v, $unpackfilter[$type]->(@v_t[$i_array_t .. ($i_array_t + $delta - 1)]);
      }
    }
  }

  $self->offset($i + $desc->{message_length});

  my $cb = $desc->{callback};

  if (ref $cb eq 'ARRAY') {
    $v[0] & $rechd_mask_compressed_timestamp_header and push @v, $self->last_timestamp + ($v[0] & $rechd_mask_cth_timestamp);
    $cb->[0]->($self, $desc, \@v, @$cb[1 .. $#$cb]);
  }
  else {
    1;
  }
}

sub pack_data_message {
  my ($self, $desc, $v) = @_;

  if (ref $desc->{packfilter_index} eq 'ARRAY') {
    my @v = ($v->[0]);
    my $piv = $desc->{packfilter_index};
    my ($i, $j);

    for ($i = 1, $j = 3 ; $j < @$piv ; $j += 4) {
      my ($type, $i_array_t, $c, $i_array) = @$piv[($j - 3) .. $j];

      $i < $i_array and push @v, @$v[$i .. ($i_array - 1)];
      $i = $i_array + $c;

      for (; $i_array < $i ; ++$i_array) {
	push @v, $packfilter[$type]->($v->[$i_array]);
      }
    }

    pack($desc->{template}, @v);
  }
  else {
    pack($desc->{template}, @$v);
  }
}

sub switched {
  my ($self, $desc, $v, $sw) = @_;
  my ($keyv, $key, $attr);

  if (ref $sw->{_by} eq 'ARRAY') {
    $keyv = $sw->{_by};
  }
  else {
    $keyv = [$sw->{_by}];
  }

  foreach $key (@$keyv) {
    my $i_name = 'i_' . $key;
    my $val;

    if (defined $desc->{$i_name} && ($val = $v->[$desc->{$i_name}]) != $desc->{'I_' . $key}) {
      my $key_tn = $desc->{'t_' . $key};

      if (defined $key_tn) {
	my $t_val = $self->named_type_value($key_tn, $val);

	$val = $t_val if defined $t_val;
      }

      if (ref $sw->{$val} eq 'HASH') {
	$attr = $sw->{$val};
	last;
      }
    }
  }

  $attr;
}

sub string_value {
  my ($self, $v, $i, $n) = @_;
  my $j;

  for ($j = 0 ; $j < $n ; ++$j) {
    $v->[$i + $j] == 0 && last;
  }

  pack('C*', @{$v}[$i .. ($i + $j - 1)]);
}

sub unit_table {
  my $self = shift;
  my $unit = shift;

  if (@_) {
    $self->{unit_table}->{$unit} = $_[0];
  }
  else {
    $self->{unit_table}->{$unit};
  }
}

sub without_unit {
  my $self = shift;

  if (@_) {
    $self->{without_unit} = $_[0];
  }
  else {
    $self->{without_unit};
  }
}

sub value_processed {
  my ($self, $num, $attr) = @_;

  if (ref $attr eq 'HASH') {
    my ($unit, $offset, $scale) = @{$attr}{qw(unit offset scale)};

    $num /= $scale if $scale > 0;
    $num -= $offset if $offset;

    if ($unit ne '') {
      my $unit_tab = $self->unit_table($unit);

      if (ref $unit_tab eq 'HASH') {
	my ($unit1, $offset1, $scale1) = @{$unit_tab}{qw(unit offset scale)};

	if ($scale1 > 0) {
	  $num /= $scale1;
	  $scale += $scale1;
	}

	$num -= $offset1 if $offset1;
	$unit = $unit1 if $unit1 ne '';
      }

      if ($scale > 0) {
	my $below_pt = int(log($scale + 9) / log(10));

	if ($self->without_unit) {
	  sprintf("%.${below_pt}f", $num);
        }
	else {
	  sprintf("%.${below_pt}f %s", $num, $unit);
        }
      }
      elsif ($self->without_unit) {
	$num;
      }
      else {
	$num . " " . $unit;
      }
    }
    elsif ($scale > 0) {
      my $below_pt = int(log($scale + 9) / log(10));

      sprintf("%.${below_pt}f", $num);
    }
    else {
      $num;
    }
  }
  else {
    $num;
  }
}

sub value_unprocessed {
  my ($self, $str, $attr) = @_;

  if (ref $attr eq 'HASH') {
    my ($unit, $offset, $scale) = @{$attr}{qw(unit offset scale)};
    my $num = $str;

    if ($unit ne '') {
      my $unit_tab = $self->unit_table($unit);

      if (ref $unit_tab eq 'HASH') {
	my ($unit1, $offset1, $scale1) = @{$unit_tab}{qw(unit offset scale)};

	$scale += $scale1 if $scale1 > 0;
	$offset += $offset1 if $offset1;
	$unit = $unit1 if $unit1 ne '';
      }

      length($num) >= length($unit) && substr($num, -length($unit)) eq $unit
	and substr($num, -length($unit)) = '';
    }

    $num += $offset if $offset;
    $num *= $scale if $scale > 0;
    $num;
  }
  else {
    $str;
  }
}

sub value_cooked {
  my ($self, $tname, $attr, $invalid, $val) = @_;

  if ($val == $invalid) {
    $val;
  }
  else {
    if ($tname ne '') {
      my $vname = $self->named_type_value($tname, $val);

      defined $vname && return $vname;
    }

    if (ref $attr eq 'HASH') {
      $self->value_processed($val, $attr);
    }
    else {
      $val;
    }
  }
}

sub value_uncooked {
  my ($self, $tname, $attr, $invalid, $val) = @_;

  if ($val !~ /^[-+]?\d+$/) {
    if ($tname ne '') {
      my $vnum = $self->named_type_value($tname, $val);

      defined $vnum && return $vnum;
    }

    if (ref $attr eq 'HASH') {
      $self->value_unprocessed($val, $attr);
    }
    else {
      $val;
    }
  }
  else {
    $val;
  }
}

sub seconds_to_hms {
  my ($self, $s) = @_;
  my $sign = 1;

  if ($s < 0) {
    $sign = -1;
    $s = -$s;
  }

  my $h = int($s / 3600);
  my $m = int(($s - $h * 3600) / 60);

  $s -= $h * 3600 + $m * 60;

  my $hms = sprintf('%s%uh%um%g', $sign < 0 ? '-' : '', $h, $m, $s);

  $hms =~ s/\./s/ or $hms .= 's';
  $hms;
}

sub initialize {
  my $self = shift;
  my $buffer = '';

  %$self =
    (
     'error' => undef,
     'file_read' => 0,
     'file_processed' => 0,
     'offset' => 0,
     'buffer' => \$buffer,
     'FH' => new FileHandle,
     'data_message_callback' => +{},
     'unit_table' => +{},
     );

  $self->data_message_callback_by_name(developer_data_id => \&syscallback_devdata_id);
  $self->data_message_callback_by_name(field_description => \&syscallback_devdata_field_desc);
  $self;
}

sub reset {
  my $self = shift;

  $self->clear_buffer;

  %$self = map {($_ => $self->{$_})} qw(error buffer FH data_message_callback unit_table
					verbose cp_fit cp_fit_FH EOF use_gmtime numeric_date_time without_unit maybe_chained);

  my $buffer = $self->buffer;

  $self->file_read(length($$buffer));
  $self->file_processed(0);
  $self;
}

sub new {
  my $class = shift;
  my $self = +{};

  bless $self, $class;
  $self->initialize(@_);
}

sub open {
  my $self = shift;
  my $fn = $self->file;

  if ($fn ne '') {
    my $FH = $self->FH;

    if ($FH->open("< $fn")) {
      if (binmode $FH, ':raw') {
	1;
      }
      else {
	$self->error("binmode \$FH, ':raw': $!");
      }
    }
    else {
      $self->error("\$FH->open(\"< $fn\"): $!");
    }
  }
  else {
    $self->error('no file name given');
  }
}

sub fetch {
  my $self = shift;

  $self->fill_buffer($crc_octets) || return undef;

  my $buffer = $self->buffer;
  my $i = $self->offset;
  my $j = $self->file_processed + $i;

  if ($j < $self->file_size) {
    my $rechd = ord(substr($$buffer, $i, 1));
    my $desc_i = -1;

    if ($rechd & $rechd_mask_compressed_timestamp_header) {
      $desc_i = ($rechd & $rechd_mask_cth_local_message_type) >> $rechd_offset_cth_local_message_type;
    }
    elsif ($rechd & $rechd_mask_definition_message) {
      $self->fetch_definition_message;
    }
    else {
      $desc_i = $rechd & $rechd_mask_local_message_type;
    }

    if ($desc_i < 0) {
      1;
    }
    else {
      my $desc = $self->data_message_descriptor->[$desc_i];

      if (ref $desc eq 'HASH') {
	$self->fetch_data_message($desc);
      }
      else {
	$self->error(sprintf("%d at %ld: not defined", $rechd, $j));
      }
    }
  }
  elsif (!$self->maybe_chained && $j > $self->file_size) {
    $self->trailing_garbages($self->trailing_garbages + length($$buffer) - $i);
    $self->offset(length($$buffer));
    1;
  }
  else {
    $self->crc_calc(length($$buffer)) if !defined $self->crc;

    my ($crc_expected, $k);

    for ($crc_expected = 0, $k = $crc_octets ; $k > 0 ;) {
      $crc_expected = ($crc_expected << 8) + ord(substr($$buffer, $i + --$k, 1));
    }

    $self->crc_expected($crc_expected);
    $self->offset($i + $crc_octets);
    $self->end_of_chunk(1);
    !$self->maybe_chained;
  }
}

@type_name = ();

$type_name[FIT_ENUM] = 'ENUM';
$type_name[FIT_SINT8] = 'SINT8';
$type_name[FIT_UINT8] = 'UINT8';
$type_name[FIT_SINT16] = 'SINT16';
$type_name[FIT_UINT16] = 'UINT16';
$type_name[FIT_SINT32] = 'SINT32';
$type_name[FIT_UINT32] = 'UINT32';
$type_name[FIT_STRING] = 'STRING';
$type_name[FIT_FLOAT32] = 'FLOAT32';
$type_name[FIT_FLOAT64] = 'FLOAT64';
$type_name[FIT_UINT8Z] = 'UINT8Z';
$type_name[FIT_UINT16Z] = 'UINT16Z';
$type_name[FIT_UINT32Z] = 'UINT32Z';
$type_name[FIT_BYTE] = 'BYTE';

sub isnan { !defined($_[0] <=> 9**9**9) }

sub print_all_fields {
  my ($self, $desc, $v, %opt) = @_;
  my ($indent, $FH, $skip_invalid) = @opt{qw(indent FH skip_invalid)};

  $FH=\*STDOUT if !defined $FH;
  $FH->print($indent, 'compressed_timestamp: ', $self->named_type_value('date_time', $v->[$#$v]), "\n") if $desc->{array_length} == $#$v;

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
      isnan($v->[$i + $j]) && next;
      $v->[$i + $j] != $invalid && last;
    }

    if ($j < $c || !$skip_invalid) {
      $self->last_timestamp($v->[$i]) if $type == FIT_UINT32 && $tname eq 'date_time' && $pname eq 'timestamp';
      $FH->print($indent, $pname, ' (', $desc->{'N_' . $name}, '-', $c, '-', $type_name[$type] ne '' ? $type_name[$type] : $type);
      $FH->print(', original name: ', $name) if $name ne $pname;
      $FH->print(', INVALID') if $j >= $c;
      $FH->print('): ');

      if ($type == FIT_STRING) {
	$FH->print("\"", $self->string_value($v, $i, $c), "\"\n");
      }
      else {
	$FH->print('{') if $c > 1;

	my $pval = $self->value_cooked($tname, $attr, $invalid, $v->[$i]);

	$FH->print($pval);
	$FH->print(' (', $v->[$i], ')') if $v->[$i] ne $pval;

	if ($c > 1) {
	  my ($j, $k);

	  for ($j = $i + 1, $k = $i + $c ; $j < $k ; ++$j) {
	    $pval = $self->value_cooked($tname, $attr, $invalid, $v->[$j]);
	    $FH->print(', ', $pval);
	    $FH->print(' (', $v->[$j], ')') if $v->[$j] ne $pval;
	  }

	  $FH->print('}');
	}

	$FH->print("\n");
      }
    }
  }

  1;
}

sub semicircles_to_degree {
  my ($self, $on) = @_;

  $self->unit_table('semicircles' => $on ? +{'unit' => 'deg', 'scale' => 2 ** 31 / 180} : undef);
}

sub mps_to_kph {
  my ($self, $on) = @_;

  $self->unit_table('m/s' => $on ? +{'unit' => 'km/h', 'scale' => 1 / 3.6} : undef);
}

sub close {
  my $self = shift;
  my $cp_fit_FH = $self->cp_fit_FH;
  my $FH = $self->FH;

  $cp_fit_FH->close if &safe_isa($cp_fit_FH, 'FileHandle') && $cp_fit_FH->opened;
  $FH->close if $FH->opened;
}

1;
__END__

=head1 NAME

Garmin::FIT - A Perl class to decode Garmin .FIT files

=head1 SYNOPSIS

  use Garmin::FIT;
  Garmin::FIT->version_string;
  $fit = new Garmin::FIT;
  $fit->unit_table(<unit> => <unit conversion table>);
  $fit->semicircles_to_degree(<boolean>);
  $fit->mps_to_kph(<boolean>);
  $fit->use_gmtime(<boolean>);
  $fit->file(<file name>)
  $fit->open;
  $fit->fetch_header;
  $fit->fetch;
  $fit->protocol_version_string;
  $fit->protocol_version_string(<version number>);
  $fit->profile_version_string;
  $fit->profile_version_string(<version number>);
  $fit->data_message_callback_by_name(<message name>, <callback function>[, <callback data>, ...]);
  $fit->data_message_callback_by_num(<message number>, <callback function>[, <callback data>, ...]);
  $fit->switched(<data message descriptor>, <array of values>, <data type table>);
  $fit->string_value(<array of values>, <offset in the array>, <counts>);
  $fit->value_cooked(<type name>, <field attributes table>, <invalid data>, <value>);
  $fit->error;
  $fit->crc_expected;
  $fit->crc;
  $fit->trailing_garbages;
  $fit->close;
  ...

=head1 DESCRIPTION

B<Garmin::FIT> is a Perl class to provide interfaces to decode Garmin .FIT files.

=for html The latest version is obtained via

=for html <blockquote>

=for html <!--#include virtual="/cgi-perl/showfile?/cycling/pub/GarminFIT-[0-9]*.tar.gz"-->.

=for html </blockquote>

There are three applications

=for html <blockquote><a href="fitdump.shtml">

C<fitdump>,

=for html </a></blockquote>

=for html <blockquote><a href="fitsed.shtml">

C<fitsed>,

=for html </a></blockquote>

and

=for html <blockquote><a href="fit2tcx.shtml">

C<fit2tcx>

=for html </a></blockquote>

using this class.

=head2 Constants

Following constants are automatically exported.

=over 4

=item FIT_ENUM

=item FIT_SINT8

=item FIT_UINT8

=item FIT_SINT16

=item FIT_UINT16

=item FIT_SINT32

=item FIT_UINT32

=item FIT_SINT64

=item FIT_UINT64

=item FIT_STRING

=item FIT_FLOAT16

=item FIT_FLOAT32

=item FIT_UINT8Z

=item FIT_UINT16Z

=item FIT_UINT32Z

=item FIT_UINT64Z

=item FIT_BYTE

numbers representing base types of field values in data messages.

=item FIT_BASE_TYPE_MAX

the maximal number representing base types of field values in data messages.

=item FIT_HEADER_LENGTH

length of a .FIT file header.

=back

=head2 Class methods

=over 4

=item Garmin::FIT-E<gt>version_string

returns a string representing the version of this class.

=item new Garmin::FIT

creates a new object and returns it.

=item Garmin::FIT-E<gt>message_name(I<<message spec>>)

returns the message name for I<<message spec>> or undef.

=item Garmin::FIT-E<gt>message_number(I<<message spec>>)

returns the message number for I<<message spec>> or undef.

=item Garmin::FIT-E<gt>field_name(I<<message spec>>, I<<field spec>>)

returns the field name for I<<field spec>> in I<<message spec>> or undef.

=item Garmin::FIT-E<gt>field_number(I<<message spec>>, I<<field spec>>)

returns the field index for I<<field spec>> in I<<message spec>> or undef.

=item Garmin::FIT-E<gt>cat_header(I<<protocol version>>, I<<profile version>>, I<<file length>>[, I<<refrencne to a scalar>>])

composes the binary form of a .FIT file header,
concatenates the scalar and it,
and returns the reference to the scalar.
If the 4th argument is omitted,
it returns the reference to the binary form.
I<<file length>> is assumed not to include the file header and trailing CRC.

=item Garmin::FIT-E<gt>crc_of_string(I<<old CRC>>, I<<reference to a scalar>>, I<<offset in scalar>>, I<<counts>>)

calculate CRC-16 of the specified part of the scalar.

=item Garmin::FIT-E<gt>my_endian

returns the endian (0 for little endian and 1 for big endian) of the machine on which this program is running.

=back

=head2 Object methods

=over 4

=item I<<object>>-E<gt>unit_table(I<<unit>> => I<<unit conversion table>>)

sets I<<unit conversion table>> for I<<unit>>.

=item I<<object>>-E<gt>semicircles_to_degree(I<<boolean>>)

a wrapper method of C<unit_table()> method.

=item I<<object>>-E<gt>mps_to_kph(I<<boolean>>)

ditto.

=item I<<object>>-E<gt>use_gmtime(I<<boolean>>)

sets the flag which of GMT or local timezone is used for C<date_time> type value conversion.

=item I<<object>>-E<gt>file(I<<file name>>)

sets the name I<<file name>> of a .FIT file.

=item I<<object>>-E<gt>open

opens the .FIT file.

=item I<<object>>-E<gt>fetch_header

reads .FIT file header, and returns an array of
the file size (excluding the trailing CRC-16), the protocol version, the profile version,
extra octets in the header other than documented 4 values, the header CRC-16 recorded in the header,
and the calculated header CRC-16.

=item I<<object>>-E<gt>fetch

reads a message in the .FIT file, and returns C<1> on success, or C<undef> on failure or EOF.

=item I<<object>>-E<gt>protocol_version_string

returns a string representing the .FIT protocol version on which this class based.

=item I<<object>>-E<gt>protocol_version_string(I<<version number>>)

returns a string representing the .FIT protocol version I<<version number>>.

=item I<<object>>-E<gt>profile_version_string

returns a string representing the .FIT protocol version on which this class based.

=item I<<object>>-E<gt>profile_version_string(I<<version number>>)

returns a string representing the .FIT profile version I<<version number>>.

=item I<<object>>-E<gt>data_message_callback_by_name(I<<message name>>, I<<callback function>>[, I<<callback data>>, ...])

register a function I<<callback function>> which is called when a data message with the name I<<message name>> is fetched.

=item I<<object>>-E<gt>data_message_callback_by_num(I<<message number>>, I<<callback function>>[, I<<callback data>>, ...])

register a function I<<callback function>> which is called when a data message with the messag number I<<message number>> is fetched.

=item I<<object>>-E<gt>switched(I<<data message descriptor>>, I<<array of values>>, I<<data type table>>)

returns real data type attributes for a C's union like field.

=item I<<object>>-E<gt>string_value(I<<array of values>>, I<<offset in the array>>, I<<counts>>)

converts an array of character codes to a Perl string.

=item I<<object>>-E<gt>value_cooked(I<<type name>>, I<<field attributes table>>, I<<invalid>>, I<<value>>)

converts I<<value>> to a (hopefully) human readable form.

=item I<<object>>-E<gt>value_uncooked(I<<type name>>, I<<field attributes table>>, I<<invalid>>, I<<value representation>>)

converts a human readable representation of a datum to an original form.

=item I<<object>>-E<gt>error

returns an error message recorded by a method.

=item I<<object>>-E<gt>crc_expected

CRC-16 attached to the end of a .FIT file.
Only available after all contents of the file has been read.

=item I<<object>>-E<gt>crc

CRC-16 calculated from the contents of a .FIT file.

=item I<<object>>-E<gt>trailing_garbages

number of octets after CRC-16, 0 usually.

=item I<<object>>-E<gt>close

closes opened file handles.

=item I<<object>>-E<gt>cat_definition_message(I<<data message descriptor>>[, I<<reference to a scalar>>])

composes the binary form of a definition message after I<<data message descriptor>>,
concatenates the scalar and it,
and returns the reference to the scalar.
If the 2nd argument is omitted,
returns the reference to the binary form.

=item I<<object>>-E<gt>endian_convert(I<<endian converter>>, I<<reference to a scalar>>, I<<offset in the scalar>>)

apply I<<endian converter>> to the specified part of the scalar.

=back

=head2 Data message descriptor

When C<fetch> method meets a definition message,
it creates a hash which includes various information about the corresponding data message.
We call the hash a data message descriptor.
It includes the following key value pairs.

=over 4

=item I<<field index>> => I<<field name>>

in a global .FIT profile.

=item C<local_message_type> => I<<local message type>>

necessarily.

=item C<message_number> => I<<message number>>

necessarily.

=item C<message_name> => I<<message name>>

only if the message is documented.

=item C<callback> => I<<reference to an array>>

of a callback function and callback data,
only if a C<callback> is registered.

=item C<endian> => I<<endian>>

of multi-octets data in this message,
where 0 for littel-endian and 1 for big-endian.

=item C<template> => I<<template for unpack>>

used to convert the binary data to an array of Perl representations.

=item C<i_>I<<field name>> => I<<offset in data array>>

of the value(s) of the field named I<<field name>>.

=item C<o_>I<<field_name>> => I<<offset in binary data>>

of the value(s) of the field named I<<field name>>.

=item C<c_>I<<field_name>> => I<<the number of values>>

of the field named I<<field name>>.

=item C<s_>I<<field_name>> => I<<size in octets>>

of whole the field named I<<field name>>
in binary data.

=item C<a_>I<<field name>> => I<<reference to a hash>>

of attributes of the field named I<<field name>>.

=item C<t_>I<<field name>> => I<<type name>>

only if the type of the value of the field named I<<field name>> has a name.

=item C<T_>I<<field name>> => I<<a number>>

representing base type of the value of the field named I<<field name>>.

=item C<N_>I<<field name>> => I<<a number>>

representing index of the filed named I<<field name>> in the global .FIT profile.

=item C<I_>I<<field name>> => I<<a number>>

representing the invalid value of the field named I<<field name>>,
that is,
if the value of the field in a binary datum equals to this number,
the field must be treated as though it does not exist in the datum.

=item C<endian_converter> => I<<reference to an array>>

used for endian conversion.

=item C<message_length> => I<<length of binary data>>

in octets.

=item C<array_length> => I<<length of data array>>

of Perl representations.

=back

=head2 Callback function

=over 4

=item When C<fetch> method meets a data message,
it calls a I<<callback function>> registered with C<data_message_callback_by_name> or C<data_message_callback_by_num>,
in the form

I<<callback function>>-E<gt>(I<<object>>, I<<data message descriptor>>, I<<array of field values>>, I<<callback data>>, ...).

=back

The return value of the function becomes the return value of C<fetch>.
It is expected to be C<1> on success, or C<undef> on failure status.

=head2 Developer data

Fields in devloper data are given names of the form I<<developer data index>>C<_>I<<converted field name>>,
and related informations are included I<<data message descriptors>> in the same way as the fields defined in the global .FIT profile.

Each I<<converted field name>> is made from the value of C<field_name> field in the corresponding I<field description message>,
after the following conversion rules:

=over 4

=item (1) Each sequence of space characters is converted to single C<_>.

=item (2) Each of remaining non-word-constituend characters is converted to C<_> + 2 column hex representation of C<ord()> of the character + C<_>.

=back

=head2 64bit data

If your perl lacks 64bit integer support,
you need the module C<Math::BigInt>.

=head1 AUTHOR

Kiyokazu SUTO E<lt>suto@ks-and-ks.ne.jpE<gt>

=head1 DISCLAIMER etc.

This program is distributed with
ABSOLUTELY NO WARRANTY.

Anyone can use, modify, and re-distibute this program
without any restriction.

=head1 ACKNOWLEDGEMENT

The author is very grateful to Garmin for supplying us free software programers with .FIT SDK
which includes detailed documetation about its proprietary file format.

=head1 CHANGES

=head2 0.22 --E<gt> 0.23

=over 4

=item C<reset()>

=item C<maybe_chained()>

=item C<end_of_chunk()>

new methods to support chained FIT files.

=item C<fetch()>

use new methods C<maybe_chained()> and C<end_of_chunk()> to support chained FIT files.

=back

=head2 0.21 --E<gt> 0.22

fixes of the issues:

=for html <blockquote><a href="https://github.com/mrihtar/Garmin-FIT/issues/1">

Problems with big endian.

=for html </a></blockquote>

=over 4

=item C<$defmsg_min_template>

the conversion specifier for I<<message number>> must be 'S', not 'v'.

=item C<endian_converter>

=item C<endian_convert()>

broken for arrays of multi-octets data.

=back

=head2 0.20 --E<gt> 0.21

=over 4

support for developer data and 64bit integers introduced in FIT 2.0.

64bit integers support is not tested at all.

This version is based on

=for html <blockquote><a href="https://github.com/mrihtar/Garmin-FIT">

Matjaz Rihtar's git repository

=for html </a></blockquote>

version.
So,
regardless of the above disclaimer,
uses, modifications, and re-distributions of this version
are restricted by the contents of the file LICENSE_LGPL_v2.1.txt in the git repository.

=back

=head2 0.14 --E<gt> 0.15

=over 4

=item C<fetch_header()>

should do initial setup of CRC explicitly.

=item C<fetch()>

code clean-up.

=back

=head2 0.13 --E<gt> 0.14

=over 4

=item C<protocol_version>

=item C<protocol_version_header_crc_started>

were wrong.

=item C<cat_definition_message()>

should take care of endianness of I<message number>.

=back

=head2 0.12 --E<gt> 0.13

=over 4

=item C<profile_version>

=item C<%named_type>

=item C<%msgtype_by_name>

follow global profile version 16.10.

=item C<fetch_data_message()>

file positions of undefined local message numbers are included in error messages.

=back

=head2 0.11 --E<gt> 0.12

=over 4

=item C<safe_isa()>

New subroutine to avoid importing C<isa> method from C<UNIVERSAL> class.
Thanks to Blaine Schmidt who kindly informed that such importation is deprecated.

=item C<xxx45_ftp>

The field numbered 45 in a session message seems FTP.

=back

=head2 0.10 --E<gt> 0.11

=over 4

=item C<profile_version>

=item C<%named_type>

=item C<%msgtype_by_name>

follow global profile version 4.10.

=back

=head2 0.09 --E<gt> 0.10

=over 4

=item C<profile_version>

=item C<%named_type>

=item C<%msgtype_by_name>

follow global profile version 2.00.

=back

=head2 0.08 --E<gt> 0.09

=over 4

=item C<profile_version>

was calculated from wrong string expression.

=item C<profile_version_from_string()>

should use C<profile_version_scale> instead of C<profile_version_major_shift>.

=item C<fetch_header()>

calculate and return header CRCs only for proper protocol version.

=item C<cat_header()>

support header CRCs and extra data in a file header.

=item C<msgtype_by_name-E<gt>{session}-E<gt>{35}>

C<scale> attribute was missing.

=item C<msgtype_by_name-E<gt>{session}-E<gt>{36}>

ditto.

=back

=head2 0.07 --E<gt> 0.08

=over 4

=item C<protocol_version>

=item C<profile_version>

=item C<fetch_header()>

=item C<%named_type>

=item C<%msgtype_by_name>

=item C<%msgtype_by_num>

follow protocol version 1.2 and global profile version 1.50.

=back

=head2 0.06 --E<gt> 0.07

=over 4

=item C<profile_version>

major and minor parts should be computed by division with scale 100,
not bit shift.

=item C<error()>

.FIT file name is included in each error message.

=item C<fetch_header()>

header length of .FIT files of newer profile version,
may differ from 12.
Thanks to report from Nils Knieling.

=back

=head2 0.05 --E<gt> 0.06

=over 4

=item C<initialize()>

the member C<buffer> of an object must be initialzied with a reference to a scalar,
not the scalar itself.
Thanks to report from Nils Knieling.

=back

=head2 0.04 --E<gt> 0.05

=over 4

=item C<$version>

=item C<$version_major_scale>

=item C<@version>

=item C<$my_endian>

=item C<$protocol_version_major_shift>

=item C<$protocol_version_minor_mask>

=item C<$protocol_version>

=item C<@protocol_version>

=item C<$profile_version_major_shift>

=item C<$profile_version_minor_mask>

=item C<$profile_version>

=item C<@profile_version>

=item C<@crc_table>

=item C<$header_template>

=item C<$header_length>

=item C<$FIT_signature_string>

=item C<$FIT_signature>

=item C<$rechd_offset_compressed_timestamp_header>

=item C<$rechd_mask_compressed_timestamp_header>

=item C<$rechd_offset_cth_local_message_type>

=item C<$rechd_length_cth_local_message_type>

=item C<$rechd_mask_cth_local_message_type>

=item C<$rechd_length_cth_timestamp>

=item C<$rechd_mask_cth_timestamp>

=item C<$rechd_offset_definition_message>

=item C<$rechd_mask_definition_message>

=item C<$rechd_length_local_message_type>

=item C<$rechd_mask_local_message_type>

=item C<$cthd_offset_local_message_type>

=item C<$cthd_length_local_message_type>

=item C<$cthd_mask_local_message_type>

=item C<$cthd_length_time_offset>

=item C<$cthd_mask_time_offset>

=item C<$defmsg_min_template>

=item C<$defmsg_min_length>

=item C<$deffld_template>

=item C<$deffld_length>

=item C<$deffld_mask_endian_p>

=item C<$deffld_mask_type>

=item C<@invalid>

=item C<@size>

=item C<@template>

=item C<%named_type>

=item C<$use_gmtime>

=item C<%msgtype_by_name>

=item C<$mesg_name_vs_num>

=item C<%msgtype_by_num>

=item C<$msgnum_anon>

=item C<$msgname_anon>

=item C<@type_name>

must be global scope,
otherwise they can be collected as garbages after long time run.
Thanks to reports and tests by Nils Knieling.

=item C<fetch_header()>

backslashs in error messages should be quoted.

=back

=head2 0.03 --E<gt> 0.04

=over 4

=item C<use_gmtime()>

works as a class method too.

=item C<msgtype_by_name-E<gt>{file_id}-E<gt>{2}>

new member C<type_name> with value C<garmin_product>.

=item C<msgtype_by_name-E<gt>{device_info}-E<gt>{4}>

ditto.

=item C<msgtype_by_name-E<gt>{schedule}-E<gt>{1}>

ditto.

=item C<msgtype_by_name-E<gt>{event}-E<gt>{data16}-E<gt>{2}-E<gt>{switch}-E<gt>{course_point}>

fix wrong member name C<when> --E<gt> C<name>.

=item C<msgtype_by_name-E<gt>{event}-E<gt>{data}-E<gt>{2}-E<gt>{switch}-E<gt>{course_point}>

ditto.

=item C<data_message_callback_by_num()>

save callback data in the form of new array reference instead of reference to C<@_>.

=item C<data_message_callback_by_name()>

ditto.

=item C<open()>

make C<$FH> binary mode.
Thanks to reports and tests on Windows platform by Nils Knieling.

=back

=head2 0.02 --E<gt> 0.03

=over 4

=item C<@EXPORT>

fix wrong name (C<FIT_FLOAT16> --E<gt> C<FIT_FLOAT64>).

=item C<my_endian()>

new method.

=item C<crc_initialize()>

removed.
CRC table initialized at top level instead.

=item C<error()>

error message tag is simplified.

=item C<crc_of_string()>

new method.

=item C<crc_calc()>

use C<crc_of_string()>.

=item C<fill_buffer()>

CRC was not calculated.

=item C<cat_header()>

new method.

=item C<named_type>-E<gt>C<{mesg_num}>

members C<undocumented_message_6> and C<undocumented_message_22> are removed.

=item C<date_string()>

new method.

=item C<named_type_value()>

change presentation of mask type type.

support reverse-conversion of 'date_time' type.

=item C<msgtype_by_name>

members C<undocumented_message_6> and C<undocumented_message_22> are removed.

=item C<msgtype_by_num>

undocumented message types are included only in this hash.

=item C<message_name()>

new method.

=item C<message_number()>

new method.

=item C<field_name()>

new method.

=item C<field_number()>

new method.

=item C<undocumented_field_name()>

new method.

=item C<fetch_definition_message()>

usage of C<offset()> method was wrong.

use C<undocumented_field_name()>.

new key-value pairs of the form C<$index =E<gt> $name> in C<%desc>.

new key-value pair C<array_length =E<gt> $i_array> in C<%desc>.

=item C<cat_definition_message()>

new method.

=item C<endian_convert()>

require more arguments (C<$buffer> and C<$i>).

=item C<last_timestamp>

new method.

=item C<fetch_data_message()>

add support for compressed timestamp headers.

=item C<switched()>

add support for multi-switching keys.

=item C<value_processed()>

change format of scaled value without unit.

=item C<value_unprocessed()>

new method.

=item C<value_cooked()>

new method.

=item C<value_uncooked()>

new method.

=item C<fetch()>

add support for compressed timestamp headers.

=item C<type_name>

array of names of types.

=item C<print_all_fields()>

add support for compressed timestamp headers.

change output format.

use single method C<value_cooked()> instead of methods C<named_type_value()> and C<value_processed()>.

=back

=head2 0.01 --E<gt> 0.02

=over 4

=item C<EOF>

new method.

=item C<fill_buffer()>

calls C<clear_buffer> method and C<EOF> method.

=item C<FIT_HEADER_LENGTH()>

new constant automatically exported.

=item C<numeric_date_time()>

new method.

=item C<named_type_value()>

sprintf format string was wrong.

calls C<numeric_date_time> method.

=item C<message_type_by_name-E<gt>{lap}>

fix typo (C<totoal_distnace> --E<gt> C<total_distance>).

=item C<message_type_by_name-E<gt>{session}>

ditto.

=item C<data_message_callback_by_name()>

fix wrong member name of C<$msgtype> (C<num> --E<gt> C<_number>).

=item C<unit_table()>

accept non hash value.

=item C<without_unit()>

new method.

=item C<value_processed()>

check whether or not C<$attr> is a hash.

calls C<without_unit> method.

=item C<initialize()>

no user defined options.

member C<crc> should not be initialized.

=item C<print_all_fields()>

=item C<semicircles_to_degree()>

=item C<mps_to_kph()>

=item C<close>

new methods.

=back

=cut
