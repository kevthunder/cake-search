
-- --------------------------------------------------------

--
-- Table structure for table `search_tables`
--

CREATE TABLE IF NOT EXISTS `search_tables` (
  `id` int(11) NOT NULL auto_increment,
  `title_for_search_fre` text character set utf8 collate utf8_unicode_ci,
  `title_fre` text character set utf8 collate utf8_unicode_ci,
  `content_fre` text character set utf8 collate utf8_unicode_ci,
  `content_for_search_fre` text character set utf8 collate utf8_unicode_ci,
  `title_eng` text character set utf8 collate utf8_unicode_ci,
  `title_for_search_eng` text character set utf8 collate utf8_unicode_ci,
  `content_for_search_eng` text character set utf8 collate utf8_unicode_ci,
  `content_eng` text character set utf8 collate utf8_unicode_ci,
  `link_fre` text character set utf8 collate utf8_unicode_ci,
  `link_eng` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `json` text character set utf8 collate utf8_unicode_ci,
  `type_fre` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `type_eng` varchar(255) default NULL,
  `plugin` varchar(255) default NULL,
  `model` varchar(255) default NULL,
  `foreign_id` int(11) default NULL,
  `active` tinyint(1) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `title_for_search_2` (`title_for_search_fre`),
  FULLTEXT KEY `content_for_search` (`content_for_search_fre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
