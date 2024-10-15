
CREATE TABLE Registrations (
    RegistrationID INT AUTO_INCREMENT PRIMARY KEY,
    EventID INT,
    ProgramID INT,
    ParticipantName VARCHAR(255) NOT NULL,
    ParticipantEmail VARCHAR(255) NOT NULL,
    ParticipantPhone VARCHAR(50) NOT NULL,
    RegistrationTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    AdditionParticipantNames TEXT,
    AdditionParticipantEmail TEXT,
    AdditionParticipantPhone TEXT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (ProgramID) REFERENCES Programs(ProgramID)
);
