
CREATE TABLE Events (
    EID INT AUTO_INCREMENT PRIMARY KEY,
    OID INT,
    EName VARCHAR(255) NOT NULL,
    ELocation VARCHAR(255) NOT NULL,
    EType VARCHAR(100),
    EImage VARCHAR(255),
    EStartDate DATETIME NOT NULL,
    EndDate DATETIME NOT NULL,
    EDecription TEXT,
    CID INT,
    Published BOOLEAN DEFAULT FALSE,
    Cancelled BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (OID) REFERENCES Organizers(OID),
    FOREIGN KEY (CID) REFERENCES Coordinators(CID)
);
