CREATE TABLE `shared_items` (
  `id_user_owner`  int(10) unsigned NOT NULL,
  `id_user_reader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - разрешён просмотр всем пользователям',
  `id_list`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - разрешён просмотр всех списков',
  `id_item`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - разрешён просмотр всех пунктов',
  UNIQUE KEY `shared_items_full_IDX` (`id_user_owner`,`id_user_reader`,`id_list`,`id_item`) USING BTREE,
         KEY `shared_items_owner_FK` (`id_user_owner`),
         KEY `shared_items_reader_FK` (`id_user_reader`),
         KEY `shared_items_list_FK` (`id_list`),
         KEY `shared_items_item_FK` (`id_item`),
  CONSTRAINT `shared_items_item_FK`   FOREIGN KEY (`id_item`)        REFERENCES `items` (`id`),
  CONSTRAINT `shared_items_list_FK`   FOREIGN KEY (`id_list`)        REFERENCES `lists` (`id`),
  CONSTRAINT `shared_items_owner_FK`  FOREIGN KEY (`id_user_owner`)  REFERENCES `users` (`id`),
  CONSTRAINT `shared_items_reader_FK` FOREIGN KEY (`id_user_reader`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Пункты, разрешённые к просмотру другими пользователями';
