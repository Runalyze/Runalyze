-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category` tinytext NOT NULL,
  `key` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `value` text NOT NULL,
  `description` tinytext NOT NULL,
  `select_description` tinytext NOT NULL,
  UNIQUE (
    `key`
  )
) ENGINE = MYISAM ;

-- --------------------------------------------------------

-- Add enum 'tool' for plugins
--

ALTER TABLE `runalyze_plugin` CHANGE `type` `type` ENUM( 'panel', 'stat', 'draw', 'tool' ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL 