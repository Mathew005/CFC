
CREATE TABLE Registrations (
    RID INT AUTO_INCREMENT PRIMARY KEY,
    EID INT,
    PID INT,
    ParticipantID INT,
    ParticipantName VARCHAR(255) NOT NULL,
    ParticipantEmail VARCHAR(255) NOT NULL,
    ParticipantPhone VARCHAR(50) NOT NULL,
    RTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    AdditionParticipantNames TEXT,
    AdditionParticipantEmail TEXT,
    AdditionParticipantPhone TEXT,
    FOREIGN KEY (EID) REFERENCES Events(EID),
    FOREIGN KEY (PID) REFERENCES Programs(PID),
    FOREIGN KEY (ParticipantID) REFERENCES Participants(PID)
);
