ALTER TABLE `#__jtg_files`
ADD `alias` varchar(255) DEFAULT '' NOT NULL,
MODIFY `title` varchar(255) NOT NULL;
UPDATE `#__content_types` 
SET table=
'{"special":{"dbtable":"#__jtg_files","key":"id","type":"Jtg_files","prefix":"Table","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Table","config":"array()"}}',
field_mappings=
'{"common": {
    "core_content_item_id": "id",
    "core_title": "title",
    "core_state": "published",
    "core_access": "access",
    "core_alias": "alias",
    "core_created_user_id": "uid",
    "core_body": "description"
  }}'
WHERE type_alias='com_jtg.file';
