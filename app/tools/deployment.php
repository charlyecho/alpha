<?php
/**
 * Created by PhpStorm.
 * User: rhinos
 * Date: 27/12/17
 * Time: 09:52
 */

return array(
    "0.1" => array(
        "CREATE TABLE app(param VARCHAR NOT NULL, val VARCHAR NOT NULL);",
    ),
    "0.2" => array(
        "INSERT INTO app(param, val) VALUES('db_version', '0');"
    ),
    "0.3" => array(
        "CREATE TABLE user(
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
            name VARCHAR UNIQUE);",
    ),
    "0.4" => array(
        "INSERT INTO user(name) VALUES('admin');",
    ),
    "0.5" => array(
        "CREATE TABLE category(
              id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
              user_id INTEGER CONSTRAINT category_user_id_fk REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE, 
              name VARCHAR, 
              color VARCHAR(6));",
    ),
    "0.6" => array(
        "CREATE TABLE subscription(
              id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
              name VARCHAR, 
              url TEXT, 
              user_id INTEGER NOT NULL CONSTRAINT subscription_user_id_fk REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE,  
              category_id INTEGER CONSTRAINT subscription_category_id_fk REFERENCES category (id), 
              last_modification_datetime DATETIME, 
              need_update ENUM(0, 1) DEFAULT 0, 
              last_check_datetime DATETIME, 
              recuperation_data_datetime DATETIME, 
              is_valid ENUM(0, 1) DEFAULT 1, 
              url_site TEXT,  
              need_parse ENUM(0, 1) DEFAULT 0 NOT NULL, 
              is_mature ENUM(0, 1) DEFAULT 0 NOT NULL);",
    ),
    "0.7" => array(
        "CREATE TABLE subscription_item (
                  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
                  subscription_id INTEGER CONSTRAINT subscription_item_subscription_id_fk REFERENCES subscription (id) ON UPDATE CASCADE ON DELETE CASCADE, 
                  local_id VARCHAR, 
                  title VARCHAR, 
                  thumbnail VARCHAR, 
                  content TEXT, 
                  link TEXT, 
                  date_time DATETIME, 
                  read ENUM(0, 1) 
                  DEFAULT 0, 
                  starred ENUM(0,1) 
                  DEFAULT 0);",
    ),
    "0.8" => array(
        "CREATE TABLE link (
                  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
                  user_id INTEGER CONSTRAINT link_user_id_fk REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE, 
                  url VARCHAR,
                  title VARCHAR, 
                  img VARCHAR, 
                  content TEXT, 
                  is_nsfw INTEGER DEFAULT 0, 
                  is_private INTEGER DEFAULT 0, 
                  creation_date DATETIME, 
                  type VARCHAR DEFAULT 'link',
                  active INTEGER DEFAULT 1,
                  tags TEXT
                  );",
    ),
    "0.9" => array(
        "ALTER TABLE user ADD background_url TEXT NULL;
            ALTER TABLE user ADD use_https_proxy INT NULL;
            ALTER TABLE user ADD share_link TEXT NULL;",
    )
);