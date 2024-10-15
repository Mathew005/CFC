
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
