
CREATE TABLE Participants (
    PID INT AUTO_INCREMENT PRIMARY KEY,
    PName VARCHAR(255) NOT NULL,
    PEmail VARCHAR(255) NOT NULL UNIQUE,
    PPassword VARCHAR(255) NOT NULL,
    PImage VARCHAR(255),
    PCode VARCHAR(50),
    PPhone VARCHAR(50),
    PCourse VARCHAR(255),
    PDepartment VARCHAR(255),
    PInstitute VARCHAR(255),
    PLocation VARCHAR(255),
    PInterests TEXT,
    PBookMarkEvent TEXT,
    PBookMarkProgram TEXT
);
