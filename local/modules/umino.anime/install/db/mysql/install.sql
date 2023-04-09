create table if not exists umino_anime_data (
    ID int(18) not null auto_increment,
    XML_ID varchar(255) not null,
    ACTIVE bool not null default true,
    DATE_CREATE datetime not null default current_timestamp,
    DATE_UPDATE datetime not null default current_timestamp on update current_timestamp,
    TITLE varchar(255) not null,
    INFO_ID int(18) not null,
    TRANSLATION_ID int(18) not null,
    EPISODES int(18) default 1,
    EPISODES_ALL int(18) default 1,
    QUALITY varchar(255),
    LINK varchar(255) not null,
    SCREENSHOTS text,
    primary key (ID));

create table if not exists umino_anime_info (
    ID int(18) not null auto_increment,
    XML_ID varchar(255) not null,
    ACTIVE bool not null default true,
    DATE_CREATE datetime not null default current_timestamp,
    DATE_UPDATE datetime not null default current_timestamp on update current_timestamp,
    TYPE varchar(255),
    TITLE varchar(255) not null,
    TITLE_ORIGINAL varchar(255),
    TITLE_OTHER text,
    YEAR varchar(255),
    SEASON int(18) default 1,
    KODIK_ID varchar(255),
    SHIKIMORI_ID varchar(255),
    WORLDART_LINK varchar(255),
    KINOPOISK_ID varchar(255),
    IMDB_ID varchar(255),
    IBLOCK_ELEMENT_ID int(18),
    primary key (ID));

create table if not exists umino_anime_translation (
    ID int(18) not null auto_increment,
    XML_ID varchar(255) not null,
    ACTIVE bool not null default true,
    DATE_CREATE datetime not null default current_timestamp,
    DATE_UPDATE datetime not null default current_timestamp on update current_timestamp,
    TITLE varchar(255) not null,
    KODIK_ID varchar(255) not null,
    TYPE varchar(255) not null,
    primary key (ID));

create table if not exists umino_anime_request (
   ID int(18) not null auto_increment,
   DATE_REQUEST datetime not null default current_timestamp,
   URL varchar(255) not null,
   TIME varchar(255),
   TOTAL int(18),
   PREV_PAGE varchar(255),
   NEXT_PAGE varchar(255),
   RESULTS_COUNT int(18) not null,
   primary key (ID));

create table if not exists umino_anime_result (
   ID int(18) not null auto_increment,
   KODIK_ID varchar(255),
   DATE_CREATE datetime not null default current_timestamp,
   DATE_UPDATE datetime not null default current_timestamp on update current_timestamp,
   DATE_IMPORT datetime,
   TYPE varchar(255) not null default 'unknown',
   LINK varchar(255) not null,
   TITLE varchar(255) not null,
   TITLE_ORIG varchar(255),
   OTHER_TITLE text,
   TRANSLATION text not null,
   YEAR varchar(255),
   LAST_SEASON int(18) default 1,
   LAST_EPISODE int(18) default 1,
   EPISODES_COUNT int(18) default 1,
   KINOPOISK_ID varchar(255),
   WORLDART_LINK varchar(255),
   SHIKIMORI_ID varchar(255),
   QUALITY varchar(255),
   CAMRIP varchar(255),
   LGBT varchar(255),
   BLOCKED_COUNTRIES text,
   BLOCKED_SEASONS text,
   CREATED_AT datetime,
   UPDATED_AT datetime,
   SCREENSHOTS text,
   REQUEST_ID int(18) not null,
   primary key (ID));

create table if not exists umino_anime_log (
  ID int(18) not null auto_increment,
  DATE_CREATE datetime not null default current_timestamp,
  FILE varchar(255) not null,
  LINE varchar(255) not null,
  MESSAGE text not null,
  primary key (ID));

create table if not exists umino_anime_episodes (
   ID int(18) not null auto_increment,
   DATE_CREATE datetime not null default current_timestamp,
   RESULT_ID int(18) not null,
   DATA_ID int(18) not null,
   SEASON int(18) not null,
   EPISODE int(18) not null,
   SEASON_LINK varchar(255) not null,
   EPISODE_LINK varchar(255) not null,
   primary key (ID));