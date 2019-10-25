DROP TABLE IF EXISTS wcf1_oauthtoken;
CREATE TABLE wcf1_oauthtoken (
	oauthtokenID VARCHAR(191) NOT NULL PRIMARY KEY,
	userID INT(10) NOT NULL,
	clientID VARCHAR(191) NOT NULL,
	time INT(10) NOT NULL,
	scope VARCHAR(255) NOT NULL,
	tokenType VARCHAR(255) NOT NULL DEFAULT 'access',
	nonce VARCHAR(255) NOT NULL DEFAULT '',
	expires INT(10) NOT NULL
);

CREATE INDEX wcf1_oauthtoken_oauthtokenID ON wcf1_oauthtoken (oauthtokenID);

DROP TABLE IF EXISTS wcf1_oauthauthorize;
CREATE TABLE wcf1_oauthauthorize (
	oauthauthorizeID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	clientID VARCHAR(191) NOT NULL,
	scope VARCHAR(255) NOT NULL,
	userID INT(10) NOT NULL,
	time INT(10) NOT NULL,
	dismiss TINYINT(3) NOT NULL DEFAULT '0',
	lastUsed INT(10) NOT NULL DEFAULT '0'
);

CREATE INDEX wcf1_oauthauthorize_clientIDuserID ON wcf1_oauthauthorize (clientID, userID);

DROP TABLE IF EXISTS wcf1_oauthclient;
CREATE TABLE wcf1_oauthclient (
	oauthclientID VARCHAR(191) NOT NULL PRIMARY KEY,
	clientSecret VARCHAR(255) NOT NULL,
	jwtSecret VARCHAR(255) NOT NULL,
	implicit TINYINT(3) NOT NULL DEFAULT '0',
	password TINYINT(3) NOT NULL DEFAULT '0',
	name VARCHAR(255) NOT NULL,
	redirectUrl VARCHAR(255) NOT NULL,
	time INT(11) NOT NULL,
	lastModified INT(11) NOT NULL
);


ALTER TABLE wcf1_oauthtoken ADD FOREIGN KEY (clientID) REFERENCES wcf1_oauthclient (oauthclientID) ON DELETE CASCADE;
ALTER TABLE wcf1_oauthtoken ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_oauthauthorize ADD FOREIGN KEY (clientID) REFERENCES wcf1_oauthclient (oauthclientID) ON DELETE CASCADE;
ALTER TABLE wcf1_oauthauthorize ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;