CREATE  TABLE `Account` (
  `AccountId` INT NOT NULL ,
  `AccountName` VARCHAR(16) NULL ,
  `Password` BINARY(16) NULL ,
  PRIMARY KEY (`AccountId`) ,
  UNIQUE INDEX `AccountName_UNIQUE` (`AccountName` ASC) );