
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
