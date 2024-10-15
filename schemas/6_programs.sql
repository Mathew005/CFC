
CREATE TABLE Programs (
    ProgramID INT AUTO_INCREMENT PRIMARY KEY,
    EventID INT,
    ProgramName VARCHAR(255) NOT NULL,
    ProgramTime TIME NOT NULL,
    ProgramLocation VARCHAR(255),
    ProgramType VARCHAR(100),
    ProgramImage VARCHAR(255),
    ProgramDate DATE NOT NULL,
    ProgramDec TEXT,
    ProgramPDF VARCHAR(255),
    ProgramFee DECIMAL(10, 2),
    ProgramMin INT,
    ProgramMax INT,
    ProgramContact INT,
    ProgramOpen BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (ProgramContact) REFERENCES Contacts(ContactID)
);
