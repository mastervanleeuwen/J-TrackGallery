INSERT INTO `#__content_types` (type_title, `table`, rules, field_mappings, router, type_alias)
VALUES ('GPS Track',
'{"special":{"dbtable":"#__jtg_files","key":"id","type":"JTG_Files","prefix":"Table","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Table","config":"array()"}}',
'',
'{"common": {
    "core_content_item_id": "id",
    "core_title": "title",
    "core_state": "published",
    "core_access": "access",
    "core_alias": null,
    "core_created_user_id": "uid",
    "core_body": "description"
  }}',
'jtgRouter::getFileRoute',
'com_jtg.file');
