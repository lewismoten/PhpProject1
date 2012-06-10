CREATE  TABLE `Account` (
  `AccountId` INT NOT NULL ,
  `AccountName` VARCHAR(16) NOT NULL ,
  `Password` VARCHAR(16) NULL ,
  `Nonce` VARCHAR(16) NULL,
  PRIMARY KEY (`AccountId`) ,
  UNIQUE INDEX `AccountName_UNIQUE` (`AccountName` ASC) );