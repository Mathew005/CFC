
CREATE TABLE Participants (
    ParticipantID INT AUTO_INCREMENT PRIMARY KEY,
    ParticipantName VARCHAR(255) NOT NULL,
    ParticipantEmail VARCHAR(255) NOT NULL UNIQUE,
    ParticipantPassword VARCHAR(255) NOT NULL,
    ParticipantPhone VARCHAR(50),
    ParticipantImage VARCHAR(255),
    ParticipantCourse VARCHAR(255),
    ParticipantDepartment VARCHAR(255),
    ParticipantInstitute VARCHAR(255),
    ParticipantLocation VARCHAR(255),
    ParticipantInterests TEXT
);
