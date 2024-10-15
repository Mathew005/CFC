tables = [
    {
        "name": "participants",
        "sql": """
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
"""
    },
    {
        "name": "organizers",
        "sql": """
CREATE TABLE Organizers (
    OrganizerID INT AUTO_INCREMENT PRIMARY KEY,
    OrganizerName VARCHAR(255) NOT NULL,
    OrganizerEmail VARCHAR(255) NOT NULL UNIQUE,
    OrganizerPassword VARCHAR(255) NOT NULL,
    OrganizerPhone VARCHAR(50),
    OrganizerImage VARCHAR(255),
    OrganizerInstitute VARCHAR(255),
    OrganizerGPS VARCHAR(100)
);
"""
    },
    {
        "name": "coordinators",
        "sql": """
CREATE TABLE Coordinators (
    CoordinatorID INT AUTO_INCREMENT PRIMARY KEY,
    Coordinator1Name VARCHAR(255),
    Coordinator1Email VARCHAR(255),
    Coordinator1Phone VARCHAR(50),
    Coordinator1Faculty VARCHAR(100),
    Coordinator2Name VARCHAR(255),
    Coordinator2Email VARCHAR(255),
    Coordinator2Phone VARCHAR(50),
    Coordinator2Faculty VARCHAR(100),
    Coordinator3Name VARCHAR(255),
    Coordinator3Email VARCHAR(255),
    Coordinator3Phone VARCHAR(50),
    Coordinator3Faculty VARCHAR(100),
    Coordinator4Name VARCHAR(255),
    Coordinator4Email VARCHAR(255),
    Coordinator4Phone VARCHAR(50),
    Coordinator4Faculty VARCHAR(100)
);
"""
    },
    {
        "name": "contacts",
        "sql": """
CREATE TABLE Contacts (
    ContactID INT AUTO_INCREMENT PRIMARY KEY,
    ContactName VARCHAR(255) NOT NULL,
    ContactEmail VARCHAR(255) NOT NULL,
    ContactPhone VARCHAR(50) NOT NULL
);
"""
    },
    {
        "name": "events",
        "sql": """
CREATE TABLE Events (
    EventID INT AUTO_INCREMENT PRIMARY KEY,
    EventName VARCHAR(255) NOT NULL,
    EventLocation VARCHAR(255) NOT NULL,
    EventType VARCHAR(100),
    EventImage VARCHAR(255),
    EventStartDate DATETIME NOT NULL,
    EventEndDate DATETIME NOT NULL,
    EventGPS VARCHAR(100),
    EventDec TEXT,
    EventContact INT,
    EventPublished BOOLEAN DEFAULT FALSE,
    EventCancelled BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (EventContact) REFERENCES Contacts(ContactID)
);
"""
    },
    {
        "name": "programs",
        "sql": """
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
"""
    },
    {
        "name": "registrations",
        "sql": """
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
"""
    }
]

# Create SQL files
for idx, table in enumerate(tables, start=1):
    filename = f"schemas\{idx}_{table['name']}.sql"
    with open(filename, "w") as file:
        file.write(f"{table['sql']}")
    print(f"Created file: {filename}")
