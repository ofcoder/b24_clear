
CREATE TABLE b_hlblock_entity (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  NAME varchar(100) NOT NULL,
  TABLE_NAME varchar(64) NOT NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE b_hlblock_entity_lang (
  ID int NOT NULL,
  LID char(2) NOT NULL,
  NAME varchar(100) NOT NULL
);

CREATE TABLE b_hlblock_entity_rights (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  HL_ID int NOT NULL,
  TASK_ID int NOT NULL,
  ACCESS_CODE varchar(50) NOT NULL,
  PRIMARY KEY (ID)
);
