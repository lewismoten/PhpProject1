CREATE  TABLE `Account` 
(
    `AccountId` INT NOT NULL ,
    `AccountName` VARCHAR(16) NOT NULL ,
    `Password` VARCHAR(100) NULL ,
    `iv` varchar(50) NULL,
    `Nonce` VARCHAR(16) NULL,
    `NonceCreated` DATETIME NULL,
    `LastCnonce` VARCHAR(16) NULL,
  PRIMARY KEY (`AccountId`) ,
  UNIQUE INDEX `AccountName_UNIQUE` (`AccountName` ASC) );