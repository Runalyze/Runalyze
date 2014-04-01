package Garmin::FIT;

use FileHandle;
use POSIX qw(BUFSIZ);
use Time::Local;
use UNIVERSAL qw(isa);

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
	     FIT_STRING
	     FIT_FLOAT32
	     FIT_FLOAT64
	     FIT_UINT8Z
	     FIT_UINT16Z
	     FIT_UINT32Z
	     FIT_BYTE
	     FIT_HEADER_LENGTH
	     );

$version = 0.11;
$version_major_scale = 100;

sub version_major {
  my ($self, $ver) = @_;

  if (wantarray) {
    (int($ver), int(($ver - int($ver)) * $version_major_scale));
  }
  else {
    int($ver);
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

$protocol_version = &protocol_version_from_string(undef, "1.3");
@protocol_version = &protocol_version_major(undef, $protocol_version);
$protocol_version_header_crc_started = &protocol_version_from_string(undef, "1.2");

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

$profile_version = &profile_version_from_string(undef, "4.10");
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

sub error {
  my $self = shift;

  if (@_) {
    my ($p, $fn, $l, $subr, $fit);

    (undef, $fn, $l) = caller(0);
    ($p, undef, undef, $subr) = caller(1);
    $fit = $self->file;
    $fit .= ': ' if $fit ne '';

    $self->{error} = "$p::$subr\#$l\@$fn: $fit$_[0]";
    undef;
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

      if (isa($FH, 'FileHandle') && $FH->opened) {
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

      ($self->file_size($f_len + $h_len), $proto_ver, $prof_ver, $extra, $crc_expected, $crc_calculated);
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

$rechd_offset_compressed_timestamp_header = 7;
$rechd_mask_compressed_timestamp_header = 1 << $rechd_offset_compressed_timestamp_header;
$rechd_offset_cth_local_message_type = 5;
$rechd_length_cth_local_message_type = 2;
$rechd_mask_cth_local_message_type = ((1 << $rechd_length_cth_local_message_type) - 1) << $rechd_offset_cth_local_message_type;
$rechd_length_cth_timestamp = $rechd_offset_cth_local_message_type;
$rechd_mask_cth_timestamp = (1 << $rechd_length_cth_timestamp) - 1;
$rechd_offset_definition_message = 6;
$rechd_mask_definition_message = 1 << $rechd_offset_definition_message;
$rechd_length_local_message_type = 4;
$rechd_mask_local_message_type = (1 << $rechd_length_local_message_type) - 1;
$cthd_offset_local_message_type = 5;
$cthd_length_local_message_type = 2;
$cthd_mask_local_message_type = (1 << $cthd_length_local_message_type) - 1;
$cthd_length_time_offset = 5;
$cthd_mask_time_offset = (1 << $cthd_length_time_offset) - 1;

$defmsg_min_template = 'C C C v C';
$defmsg_min_length = length(pack($defmsg_min_template));

$deffld_template = 'C C C';
$deffld_length = length(pack($deffld_template));
$deffld_mask_endian_p = 1 << 7;
$deffld_mask_type = (1 << 5) - 1;

@invalid = (0xFF) x ($deffld_mask_type + 1);

$invalid[FIT_SINT8] = 0x7F;
$invalid[FIT_SINT16] = 0x7FFF;
$invalid[FIT_UINT16] = 0xFFFF;
$invalid[FIT_SINT32] = 0x7FFFFFFF;
$invalid[FIT_UINT32] = 0xFFFFFFFF;
$invalid[FIT_STRING] = $invalid[FIT_UINT8Z] = $invalid[FIT_UINT16Z] = $invalid[FIT_UINT32Z] = 0;
$invalid[FIT_FLOAT32] = unpack('f', pack('V', 0xFFFFFFFF));
$invalid[FIT_FLOAT64] = unpack('d', pack('V V', 0xFFFFFFFF, 0xFFFFFFFF));

sub invalid {
  my ($self, $type) = @_;

  $invalid[$type & $deffld_mask_type];
}

@size = (1) x ($deffld_mask_type + 1);

$size[FIT_SINT16] = $size[FIT_UINT16] = $size[FIT_UINT16Z] = 2;
$size[FIT_SINT32] = $size[FIT_UINT32] = $size[FIT_UINT32Z] = $size[FIT_FLOAT32] = 4;
$size[FIT_FLOAT64] = 8;

@template = ('C') x ($deffld_mask_type + 1);

$template[FIT_SINT8] = 'c';
$template[FIT_SINT16] = 's';
$template[FIT_UINT16] = $template[FIT_UINT16Z] = 'S';
$template[FIT_SINT32] = 'l';
$template[FIT_UINT32] = $template[FIT_UINT32Z] = 'L';
$template[FIT_FLOAT32] = 'f';
$template[FIT_FLOAT64] = 'd';

%named_type =
  (

   'file' => +{
     '_base_type' => FIT_ENUM,
     'device' => 1,
     'setting' => 2,
     'sport' => 3,
     'activity' => 4,
     'workout' => 5,
     'course' => 6,
     'schedule' => 7,
     'monitoring' => 9,
     'totals' => 10,
     'goals' => 11,
     'blood_pressure' => 14,
     'monitoring' => 15,
     'activity_summary' => 20,
     'monitoring_daily' => 28,
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
     'goal' => 15,
     'session' => 18,
     'lap' => 19,
     'record' => 20,
     'event' => 21,
     'device_info' => 23,
     'workout' => 26,
     'workout_step' => 27,
     'schedule' => 28,
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
     'hrv' => 78,
     'length' => 101,
     'monitoring_info' => 103,
     'pad' => 105,
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
     '_min' => 0x10000000,
     '_out_of_range' => 'seconds from device power on',
     '_offset' => -timegm(0, 0, 0, 31, 11, 1989), # 1989-12-31 00:00:00 GMT
   },

   'local_date_time' => +{
     '_base_type' => FIT_UINT32,
   },

   'message_index' => +{
     '_base_type' => FIT_UINT16,
     '_mask' => 1,
     'selected' => 0x8000,
     'reserved' => 0x7000,
     'mask' => 0x0FFF,
   },

   'device_index' => +{
     '_base_type' => FIT_UINT8,
     'creator' => 0,
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
     'custom' => 254,
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

   'switch' => +{
     '_base_type' => FIT_ENUM,
     'off' => 0,
     'on' => 1,
     'auto' => 2,
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
     'all' => 254,
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
     'rotor' => 60,
     'geonaute' => 61,
     'id_bike' => 62,
     'development' => 255,
     'actigraphcorp' => 5759,
   },

   'garmin_product' => +{
     '_base_type' => FIT_UINT16,
     'hrm_bike' => 0,
     'hrm1' => 1,
     'axh01' => 2,
     'axb01' => 3,
     'axb02' => 4,
     'hrm2ss' => 5,
     'dsi_alf02' => 6,
     'fr405' => 717,
     'fr50' => 782,
     'fr60' => 988,
     'dsi_alf01' => 1011,
     'fr310xt' => 1018,
     'edge500' => 1036,
     'fr110' => 1124,
     'edge800' => 1169,
     'chirp' => 1253,
     'edge200' => 1325,
     'fr910xt' => 1328,
     'alf04' => 1341,
     'fr610' => 1345,
     'fr70' => 1436,
     'fr310xt_4t' => 1446,
     'amx' => 1461,
     'sdm4' => 10007,
     'training_center' => 20119,
     'connect' => 65534,
   },

   'device_type' => +{
     '_base_type' => FIT_UINT8,
     'antfs' => 1,
     'bike_power' => 11,
     'environment_sensor' => 12,
     'multi_sport_speed_distance' => 15,
     'fitness_equipment' => 17,
     'blood_pressure' => 18,
     'weight_scale' => 119,
     'hrm' => 120,
     'bike_speed_cadence' => 121,
     'bike_cadence' => 122,
     'bike_speed' => 123,
     'stride_speed_distance' => 124,
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

   'crank_length' => +{
     '_base_type' => FIT_UINT8,
     'auto' => 0xFE,
   },

   );

my $typdesc;

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
	$expr =~s/^.*=//;

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

   'file_id' => +{
     0 => +{'name' => 'type', 'type_name' => 'file'},
     1 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     2 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     3 => +{'name' => 'serial_number'},
     4 => +{'name' => 'time_created', 'type_name' => 'date_time'},
     5 => +{'name' => 'number'},
   },

   'file_creator' => +{
     0 => +{'name' => 'software_version'},
     1 => +{'name' => 'hardware_version'},
   },

   'pad' => +{
     0 => +{'name' => 'pad'},
   },

   'software' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},

     1 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     3 => +{'name' => 'version', 'scale' => 100},
     5 => +{'name' => 'part_number'},
   },

   'capabilities' => +{
     0 => +{'name' => 'languages'},
     1 => +{'name' => 'sports', 'type_name' => 'sport_bits_0'},
     21 => +{'name' => 'workout_supported', 'type_name' => 'workout_capabilities'},
   },

   'file_capabilities' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'type', 'type_name' => 'file'},
     1 => +{'name' => 'flags', 'type_name' => 'file_flags'},
     2 => +{'name' => 'directory'},
     3 => +{'name' => 'max_count'},
     4 => +{'name' => 'max_size'},
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
     4 => +{'name' => 'bits'},
   },

   'device_settings' => +{
     1 => +{'name' => 'utc_offset'},
   },

   'user_profile' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'friendly_name'},
     1 => +{'name' => 'gender', 'type_name' => 'gender'},
     2 => +{'name' => 'age'},
     3 => +{'name' => 'height', scale => 100, 'unit' => 'm'},
     4 => +{'name' => 'weight', scale => 10, 'unit' => 'kg'},
     5 => +{'name' => 'language', 'type_name' => 'language'},
     6 => +{'name' => 'elev_setting', 'type_name' => 'display_measure'},
     7 => +{'name' => 'weight_setting', 'type_name' => 'display_measure'},
     8 => +{'name' => 'resting_heart_rate', 'unit' => 'bpm'},
     9 => +{'name' => 'default_running_max_heart_rate', 'unit' => 'bpm'},
     10 => +{'name' => 'default_biking_max_heart_rate', 'unit' => 'bpm'},
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
     19 => +{'name' => 'crank_length', 'type_name' => 'crank_length', 'scale' => 2, 'offset' => -110, 'unit' => 'mm'},
     20 => +{'name' => 'enabled'},
     21 => +{'name' => 'bike_spd_ant_id_trans_type'},
     22 => +{'name' => 'bike_cad_ant_id_trans_type'},
     23 => +{'name' => 'bike_spdcad_ant_id_trans_type'},
     24 => +{'name' => 'bike_power_ant_id_trans_type'},
   },

   'zones_target' => +{
     1 => +{'name' => 'max_heart_rate', 'unit' => 'bpm'},
     2 => +{'name' => 'threshold_heart_rate', 'unit' => 'bpm'},
     3 => +{'name' => 'functional_threshold_power', 'unit' => 'w'},
     5 => +{'name' => 'hr_calc_type', 'type_name' => 'hr_zone_calc'},
     7 => +{'name' => 'pwr_calc_type', 'type_name' => 'power_zone_calc'},
   },

   'sport' => +{
     0 => +{'name' => 'sport', 'type_name' => 'sport'},
     1 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     3 => +{'name' => 'name'},
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

   'power_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'high_value', 'unit' => 'watts'},
     2 => +{'name' => 'name'},
   },

   'met_zone' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'high_bpm'},
     2 => +{'name' => 'calories', 'scale' => 10, 'unit' => 'kcal/min'},
     3 => +{'name' => 'fat_calories', 'scale' => 10, 'unit' => 'kcal/min'},
   },

   'goal' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     0 => +{'name' => 'sport', 'type_name' => 'sport'},
     1 => +{'name' => 'sub_sport', 'type_name' => 'sub_sport'},
     2 => +{'name' => 'start_date', 'type_name' => 'date_time'},
     3 => +{'name' => 'end_date', 'type_name' => 'date_time'},
     4 => +{'name' => 'type', 'type_name' => 'goal'},
     5 => +{'name' => 'value'},
     6 => +{'name' => 'repeat'},
     7 => +{'name' => 'target_value'},
     8 => +{'name' => 'recurrence', 'type_name' => 'goal_recurrence'},
     9 => +{'name' => 'recurrence_value'},
     10 => +{'name' => 'enabled'},
   },

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
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
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
       'name' => 'total_cycles',
       'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'sport',
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
       'name' => 'avg_cadence',
       'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'running' => +{'name' => 'avg_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'avg_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     19 => +{
       'name' => 'max_cadence',
       'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
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
     37 => +{'name' => 'left_right_balance', 'type_name' => left_right_balance_100},
     41 => +{'name' => 'avg_stroke_count', 'scale' => 10},
     42 => +{'name' => 'avg_stroke_distance', 'scale' => 100, 'unit' => 'm'},
     43 => +{'name' => 'swim_stroke', 'type_name' => 'swim_stroke'},
     44 => +{'name' => 'pool_length', 'scale' => 100, 'unit' => 'm'},
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
   },

   'lap' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
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
       'name' => 'total_cycles',
       'unit' => 'cycles',

       'switch' => +{
	 '_by' => 'sport',
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
       'name' => 'avg_cadence',
       'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
	 'running' => +{'name' => 'avg_running_cadence', 'unit' => 'strides/min'},
	 'swimming' => +{'name' => 'avg_swimming_cadence', 'unit' => 'strokes/min'},
       },
     },

     18 => +{
       'name' => 'max_cadence',
       'unit' => 'rpm',

       'switch' => +{
	 '_by' => 'sport',
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
     27 => +{'name' => 'nec_lat', 'unit' => 'semicircles'},
     28 => +{'name' => 'nec_long', 'unit' => 'semicircles'},
     29 => +{'name' => 'swc_lat', 'unit' => 'semicircles'},
     30 => +{'name' => 'swc_long', 'unit' => 'semicircles'},
     32 => +{'name' => 'num_lengths', 'unit' => 'lengths'},
     33 => +{'name' => 'normalized_power', 'unit' => 'watts'},
     34 => +{'name' => 'left_right_balance', 'type_name' => 'left_right_balance_100'},
     35 => +{'name' => 'first_length_index'},
     37 => +{'name' => 'avg_stroke_distance', 'unit' => 'm'},
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
     71 => +{'name' => 'wkt_step_index'},
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
   },

   'record' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     1 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     2 => +{'name' => 'altitude', 'scale' => 5, 'offset' => 500, 'unit' => 'm'},
     3 => +{'name' => 'heart_rate', 'unit' => 'bpm'},
     4 => +{'name' => 'cadence', 'unit' => 'rpm'},
     5 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     6 => +{'name' => 'speed', 'scale' => 1000, 'unit' => 'm/s'},
     7 => +{'name' => 'power', 'unit' => 'w'},
     8 => +{'name' => 'compressed_speed_distance'},
     9 => +{'name' => 'grade', 'scale' => 100, 'unit' => '%'},
     10 => +{'name' => 'registance'},
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
   },

   'event' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'event', 'type_name' => 'event'},
     1 => +{'name' => 'event_type', 'type_name' => 'event_type'},

     2 => +{
       'name' => 'data16',

       'switch' => +{
	 '_by' => 'event',
	 'timer' => +{'name' => 'timer_trigger', 'type_name' => 'timer_trigger'},
	 'course_point' => +{'name' => 'course_point'},
	 'battery' => +{'name' => 'battery_level', 'scale' => 1000, 'unit' => 'V'},
	 'virtual_partner_pace' => +{'name' => 'virtual_partner_speed', 'scale' => 1000, 'unit' => 'm/s'},
	 'hr_high_alert' => +{'name' => 'hr_high_alert', 'unit' => 'bpm'},
	 'hr_low_alert' => +{'name' => 'hr_low_alert', 'unit' => 'bpm'},
	 'speed_high_alert' => +{'name' => 'speed_high_alert', 'scale' => 1000, 'unit' => 'm/s'},
	 'speed_low_alert' => +{'name' => 'speed_low_alert', 'scale' => 1000, 'unit' => 'm/s'},
	 'cad_high_alert' => +{'name' => 'cad_high_alert', 'unit' => 'rpm'},
	 'cad_low_alert' => +{'name' => 'cad_low_alert', 'unit' => 'rpm'},
	 'power_high_alert' => +{'name' => 'power_high_alert', 'unit' => 'w'},
	 'power_low_alert' => +{'name' => 'power_low_alert', 'unit' => 'w'},
	 'fitness_equipment' => +{'name' => 'fitness_equipment_state', 'type_name' => 'fitness_equipment_state'},
       },
     },

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
	 'power_high_alert' => +{'name' => 'power_high_alert', 'unit' => 'w'},
	 'power_low_alert' => +{'name' => 'power_low_alert', 'unit' => 'w'},
	 'time_duration_alert' => +{'name' => 'time_duration_alert', 'scale' => 1000, 'unit' => 's'},
	 'distance_duration_alert' => +{'name' => 'distance_duration_alert', 'scale' => 100, 'unit' => 'm'},
	 'calorie_duration_alert' => +{'name' => 'calorie_duration_alert', 'unit' => 'calories'},
	 'fitness_equipment' => +{'name' => 'fitness_equipment_state', 'type_name' => 'fitness_equipment_state'},
       },
     },

     4 => +{'name' => 'event_group'},
   },

   'device_info' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'device_index', 'type_name' => 'device_index'},
     1 => +{'name' => 'device_type', 'type_name' => 'device_type'},
     2 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},
     3 => +{'name' => 'serial_number'},

     4 => +{
       'name' => 'product',

       'switch' => +{
	 '_by' => 'manufacturer',
	 'garmin' => +{'name' => 'garmin_product', 'type_name' => 'garmin_product'},
       },
     },

     5 => +{'name' => 'software_version', 'scale' => 100},
     6 => +{'name' => 'hardware_version'},
     7 => +{'name' => 'cum_operating_time', 'unit' => 's'},
     10 => +{'name' => 'battery_voltage', 'scale' => 256, 'unit' => 'v'},
     11 => +{'name' => 'battery_status', 'type_name' => 'battery_status'},
   },

   'hrv' => +{
     0 => +{'name' => 'time', 'scale' => 1000, 'unit' => 's'},
   },

   'course' => +{
     4 => +{'name' => 'sport', 'type_name' => 'sport'},
     5 => +{'name' => 'name'},
     6 => +{'name' => 'capabilities', 'type_name' => 'course_capabilities'},
   },

   'course_point' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     1 => +{'name' => 'time'},
     2 => +{'name' => 'position_lat', 'unit' => 'semicircles'},
     3 => +{'name' => 'position_long', 'unit' => 'semicircles'},
     4 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     5 => +{'name' => 'type', 'type_name' => 'course_point'},
     6 => +{'name' => 'name'},
   },

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
	 'distance' => +{'name' => 'duration_distance', 'scale' => 100, 'unit' => 'm'},
	 'hr_less_than' => +{'name' => 'duration_hr'},
	 'hr_greater_than' => +{'name' => 'duration_hr'},
	 'calories' => +{'name' => 'duration_calories', 'unit' => 'kcal'},
	 'repeat_until_steps_cmplt' => +{'name' => 'duration_step'},
	 'repeat_until_time' => +{'name' => 'duration_step'},
	 'repeat_until_distance' => +{'name' => 'duration_step'},
	 'repeat_until_calories' => +{'name' => 'duration_step'},
	 'repeat_until_hr_less_than' => +{'name' => 'duration_step'},
	 'repeat_until_hr_greater_than' => +{'name' => 'duration_step'},
	 'repeat_until_power_less_than' => +{'name' => 'duration_step'},
	 'repeat_until_power_greater_than' => +{'name' => 'duration_step'},
	 'power_less_than' => +{'name' => 'duration_power', 'unit' => 'w'},
	 'power_greater_than' => +{'name' => 'duration_power', 'unit' => 'w'},
       },
     },

     3 => +{'name' => 'target_type', 'type_name' => 'wkt_step_target'},

     4 => +{
       'name' => 'target_value',

       'switch' => +{
	 '_by' => [qw(target_type duration_type)],
	 'speed' => +{'name' => 'target_speed_zone'},
	 'heart_rate' => +{'name' => 'target_hr_zone'},
	 'cadence' => +{'name' => 'target_cadence_zone'},
	 'power' => +{'name' => 'target_power_zone'},
	 'repeat_until_steps_cmplt' => +{'name' => 'repeat_steps'},
	 'repeat_until_time' => +{'name' => 'repeat_time', 'scale' => 1000, 'unit' => 's'},
	 'repeat_until_distance' => +{'name' => 'repeat_distance', 'scale' => 100, 'unit' => 'm'},
	 'repeat_until_calories' => +{'name' => 'repeat_calories', 'unit' => 'kcal'},
	 'repeat_until_hr_less_than' => +{'name' => 'repeat_hr'},
	 'repeat_until_hr_greater_than' => +{'name' => 'repeat_hr'},
	 'repeat_until_power_less_than' => +{'name' => 'repeat_power'},
	 'repeat_until_power_greater_than' => +{'name' => 'repeat_power'},
       },
     },

     5 => +{
       'name' => 'custom_target_value_low',

       'switch' => +{
	 '_by' => 'target_type',
	 'speed' => +{'name' => 'custom_target_speed_low', 'scale' => 1000, 'unit' => 'm/s'},
	 'heart_rate' => +{'name' => 'custom_target_heart_rate_low'},
	 'cadence' => +{'name' => 'custom_target_cadence_low', 'unit' => 'rpm'},
	 'power' => +{'name' => 'custom_target_power_low'},
       },
     },

     6 => +{
       'name' => 'custom_target_value_high',

       'switch' => +{
	 '_by' => 'target_type',
	 'speed' => +{'name' => 'custom_target_speed_high', 'scale' => 1000, 'unit' => 'm/s'},
	 'heart_rate' => +{'name' => 'custom_target_heart_rate_high'},
	 'cadence' => +{'name' => 'custom_target_cadence_high', 'unit' => 'rpm'},
	 'power' => +{'name' => 'custom_target_power_high'},
       },
     },

     7 => +{'name' => 'intensity', 'type_name' => 'intensity'},
   },

   'schedule' => +{
     0 => +{'name' => 'manufacturer', 'type_name' => 'manufacturer'},
     1 => +{'name' => 'product', 'type_name' => 'garmin_product'},
     2 => +{'name' => 'serial_number'},
     3 => +{'name' => 'time_created', 'type_name' => 'date_time'},
     4 => +{'name' => 'completed'},
     5 => +{'name' => 'type', 'type_name' => 'schedule'},
     6 => +{'name' => 'schedule_time', 'type_name' => 'date_time'},
   },

   'totals' => +{
     254 => +{'name' => 'message_index', 'type_name' => 'message_index'},
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'timer_time'},
     1 => +{'name' => 'distance'},
     2 => +{'name' => 'calories'},
     3 => +{'name' => 'sport', 'type_name' => 'sport'},
     4 => +{'name' => 'elapsed_time', 'unit' => 's'},
     5 => +{'name' => 'sessions'},
     6 => +{'name' => 'active_time', 'unit' => 's'},
   },

   'weight_scale' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
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

   'monitoring_info' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
   },

   'monitoring' => +{
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time', 'unit' => 's'},
     0 => +{'name' => 'device_index', 'type_name' => 'device_index'},
     1 => +{'name' => 'calories', 'unit' => 'kcal'},
     2 => +{'name' => 'distance', 'scale' => 100, 'unit' => 'm'},
     3 => +{'name' => 'cycles', 'unit' => 'cycles'},
     4 => +{'name' => 'active_time', 'scale' => 1000, 'unit' => 's'},
     5 => +{'name' => 'activity_type', 'type_name' => 'activity_type'},
     6 => +{'name' => 'activity_subtype', 'type_name' => 'activity_subtype'},
     8 => +{'name' => 'compressed_distance', 'scale' => 100, 'unit' => 'm'},
     9 => +{'name' => 'compressed_cycles', 'unit' => 'cycles'},
     11 => +{'name' => 'local_timestamp', 'type_name' => 'local_date_time'},
   },

   );

$mesg_name_vs_num = $named_type{mesg_num};

%msgtype_by_num =
  (

   22 => +{
     '_number' => 22,
     253 => +{'name' => 'timestamp', 'type_name' => 'date_time'},
     0 => +{'name' => 'xxx0_distance_source'}, # device_index in device_info
     1 => +{'name' => 'xxx1_speed_source'}, # device_index in device_info
     2 => +{'name' => 'xxx2_cadence_source'}, # device_index in device_info
     3 => +{'name' => 'xxx3_altitude_source'}, # device_index in device_info
     4 => +{'name' => 'xxx4_heart_rate_source'}, # device_index in device_info
     6 => +{'name' => 'xxx6_power_source'}, # device_index in device_info
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

	$res{$num} = ref $cb eq 'ARRAY' ? [@$cb] : [];
      }

      \%res;
    }
  }
  elsif (!defined($msgtype = $msgtype_by_num{$num})) {
    $self->error("$num is not a message type number");
  }
  elsif (@_) {
    if (ref $_[0] eq 'CODE') {
      $cbmap->{$msgtype->{_name}} = $cbmap->{$num} = [@_];
    }
    else {
      $self->error('not a CODE');
    }
  }
  else {
    my $cb = $cbmap->{$num};

    if (ref $cb eq 'ARRAY') {
      [@$cb];
    }
    else {
      [];
    }
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

	$res{$name} = ref $cb eq 'ARRAY' ? [@$cb] : [];
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

    if (ref $cb eq 'ARRAY') {
      [@$cb];
    }
    else {
      [];
    }
  }
}

sub undocumented_field_name {
  my ($self, $index, $size, $type, $i_string) = @_;

#  'xxx' . $i_string . '_' . $index . '_' . $size . '_' . $type;
  'xxx' . $index;
}

sub fetch_definition_message {
  my $self = shift;

  $self->fill_buffer($defmsg_min_length) || return undef;

  my $buffer = $self->buffer;
  my $i = $self->offset;
  my ($rechd, $reserved, $endian, $msgnum, $nfields) = unpack($defmsg_min_template, substr($$buffer, $i, $defmsg_min_length));

  $endian = 1 if $endian;
  $self->offset($i + $defmsg_min_length);

  my $len = $nfields * $deffld_length;

  $self->fill_buffer($len) || return undef;
  $i = $self->offset;
  $msgnum = unpack('n', pack('v', $msgnum)) if $endian != $my_endian;

  my $msgtype = $msgtype_by_num{$msgnum};
  my $cbmap = $self->data_message_callback;
  my $e = $i + $len;
  my ($cb, %desc, $i_array, $i_string, @cvt);

  $desc{local_message_type} = $rechd & $rechd_mask_local_message_type;
  $desc{message_number} = $msgnum;
  $desc{message_name} = $msgtype->{_name} if ref $msgtype eq 'HASH' && exists $msgtype->{_name};
  $cb = $cbmap->{$msgnum} if ref $cbmap->{$msgnum} eq 'ARRAY';
  $cb = $cbmap->{$msgnum_anon} if ref $cb ne 'ARRAY';
  $desc{callback} = $cb if ref $cb eq 'ARRAY';
  $desc{endian} = $endian;
  $desc{template} = 'C';
  $self->data_message_descriptor->[$desc{local_message_type}] = \%desc;

  for ($i_array = $i_string = 1 ; $i + $deffld_length <= $e ; $i += $deffld_length) {
    my ($index, $size, $type) = unpack($deffld_template, substr($$buffer, $i, $deffld_length));
    my ($name, $tname, %attr);

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

    if ($endian != $my_endian && $size[$type] > 1) {
      my ($p, $unp, $n);

      if ($size[$type] == 2) {
	($p, $unp) = (qw(n v));
      }
      elsif ($size[$type] == 4) {
	($p, $unp) = (qw(N V));
      }
      else {
	($p, $unp, $n) = (qw(N V), 2 * $c);
      }

      push @cvt, $p . $n, $unp . $n, $i_string, $size[$type] * $c;
    }

    $i_array += $c;
    $i_string += $size;
    $desc{template} .= ' ' . $template[$type];
    $desc{template} .= $c if $c > 1;
  }

  $desc{endian_converter} = \@cvt if @cvt;
  $desc{message_length} = $i_string;
  $desc{array_length} = $i_array;
  $self->offset($e);
}

sub cat_definition_message {
  my ($self, $desc, $p) = @_;

  if (!defined $p) {
    my $bin = '';

    $p = \$bin;
  }

  my @i_name = sort {$desc->{$a} <=> $desc->{$b}} grep {/^i_/} keys %$desc;

  $$p .= pack($defmsg_min_template, $desc->{local_message_type} | $rechd_mask_definition_message, 0, @{$desc}{qw(endian message_number)}, $#i_name + 1);

  my $i_name;

  while (@i_name) {
    my $name = shift @i_name;

    $name =~ s/^i_//;

    my $size = $desc->{'s_' . $name};

    $$p .= pack($deffld_template, $desc->{'N_' . $name}, $desc->{'c_' . $name} * $size, $desc->{'T_' . $name} | ($size > 1 ? $deffld_mask_endian_p : 0));
  }

  $p;
}

sub endian_convert {
  my ($self, $cvt, $buffer, $i) = @_;
  my $j;

  for ($j = 3 ; $j < @$cvt ; $j += 4) {
    my ($b, $n) = @$cvt[$j - 1, $j];

    $b += $i;

    my @v = unpack($cvt->[$j - 2], substr($$buffer, $b, $n));
    my ($k, $l);

    for ($k = 0, $l = $#v ; $k < $l ; ++$k, --$l) {
      @v[$k, $l] = @v[$l, $k];
    }

    substr($$buffer, $b, $n) = pack($cvt->[$j - 3], @v);
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
  my @v = unpack($desc->{template}, substr($$buffer, $i, $desc->{message_length}));

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

	sprintf("%.${below_pt}f%s", $num, $self->without_unit ? '' : $unit);
      }
      elsif ($self->without_unit) {
	$num;
      }
      else {
	$num . $unit;
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

  if ($self->file_processed + $i < $self->file_size) {
    my $rechd = ord(substr($$buffer, $i, 1));

    if ($rechd & $rechd_mask_compressed_timestamp_header) {
      my $desc = $self->data_message_descriptor->[($rechd & $rechd_mask_cth_local_message_type) >> $rechd_offset_cth_local_message_type];

      if (ref $desc eq 'HASH') {
	$self->fetch_data_message($desc);
      }
      else {
	$self->error("$rechd: not defined");
      }
    }
    elsif ($rechd & $rechd_mask_definition_message) {
      $self->fetch_definition_message;
    }
    else {
      my $desc = $self->data_message_descriptor->[$rechd & $rechd_mask_local_message_type];

      if (ref $desc eq 'HASH') {
	$self->fetch_data_message($desc);
      }
      else {
	$self->error("$rechd: not defined");
      }
    }
  }
  elsif ($self->file_processed + $i > $self->file_size) {
    $self->trailing_garbages($self->trailing_garbages + length($$buffer) - $i);
    $self->offset(length($$buffer));
  }
  else {
    $self->crc_calc(length($$buffer)) if !defined $self->crc;

    my ($crc_expected, $j);

    for ($crc_expected = 0, $j = $crc_octets ; $j > 0 ;) {
      $crc_expected = ($crc_expected << 8) + ord(substr($$buffer, $i + --$j, 1));
    }

    $self->crc_expected($crc_expected);
    $self->offset($i + $crc_octets);
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
      $v->[$i + $j] != $invalid && last;
    }

    if ($j < $c || !$skip_invalid) {
      $self->last_timestamp($v->[$i]) if $type == FIT_UINT32 && $tname eq 'date_time' && $pname eq 'timestamp';
      $FH->print($indent, $pname, ' (', $desc->{'N_' . $name}, '-', $c, '-', $type_name[$type] ne '' ? $type_name[$type] : $type);
      $FH->print(', orignal name: ', $name) if $name ne $pname;
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

  $cp_fit_FH->close if isa($cp_fit_FH, 'FileHandle') && $cp_fit_FH->opened;
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

=item FIT_STRING

=item FIT_FLOAT16

=item FIT_FLOAT32

=item FIT_UINT8Z

=item FIT_UINT16Z

=item FIT_UINT32Z

=item FIT_BYTE

numbers representing types of field values in data messages.

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
it calls a II<<callback function>> registered with C<data_message_callback_by_name> or C<data_message_callback_by_num>,
in the form

I<<callback function>>-E<gt>(I<<object>>, I<<data message descriptor>>, I<<array of field values>>, I<<callback data>>, ...).

=back

The return value of the function becomes the return value of C<fetch>.
It is expected to be C<1> on success, or C<undef> on failure status.

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
