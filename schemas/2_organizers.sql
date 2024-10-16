
CREATE TABLE Organizers (
    OrganizerID INT AUTO_INCREMENT PRIMARY KEY,
    OrganizerName VARCHAR(255) NOT NULL,
    OrganizerEmail VARCHAR(255) NOT NULL UNIQUE,
    OrganizerPassword VARCHAR(255) NOT NULL,
    OrganizerImage VARCHAR(255),
    OrganizerWebsite VARCHAR(255),
    OrganizerAddress VARCHAR(255),
    OrganizerPhone VARCHAR(50),
    OrganizerImage VARCHAR(255),
    OrganizerInstitute VARCHAR(255),
    OrganizerGPS VARCHAR(100)
);
