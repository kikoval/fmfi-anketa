SET foreign_key_checks = 0;

CREATE TABLE IF NOT EXISTS UserFinal
( id	 int NOT NULL AUTO_INCREMENT, 
tid	 int, 
login varchar(255),
givenName varchar(255), 
familyName varchar(255),
displayName varchar(255), 
department_id int, 
PRIMARY KEY (id) );

INSERT INTO UserFinal
SELECT u.id, t.id as tid, u.userName as login, t.givenName, t.familyName,u.displayName,t.department_id 
FROM User u, Teacher t 
WHERE u.userName=t.login;

INSERT INTO UserFinal(id, login, displayname)
SELECT u.id, u.userName as login, u.displayname
FROM User u
WHERE u.userName NOT IN ( SELECT uf.login FROM UserFinal uf);

INSERT INTO UserFinal
SELECT NULL, t.id, t.login, t.givenName, t.familyName, t.displayName, t.department_id
FROM Teacher t
WHERE t.login NOT IN ( SELECT uf.login FROM UserFinal uf);

UPDATE Response r
SET r.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = r.teacher_id);

UPDATE TeachersSubjects ts
SET ts.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = ts.teacher_id);

UPDATE TeachingAssociation ta
SET ta.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = ta.teacher_id);

ALTER TABLE UserFinal DROP COLUMN tid;

DROP TABLE User;

DROP TABLE Teacher;

RENAME TABLE UserFinal TO User;
