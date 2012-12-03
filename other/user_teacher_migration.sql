CREATE TABLE IF NOT EXISTS UserFinal
( id	 int NOT NULL AUTO_INCREMENT, 
tid	 int, 
login varchar(255) UNIQUE,
givenName varchar(255), 
familyName varchar(255),
displayName varchar(255), 
department_id int, 
INDEX(tid),
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

##nazov foreign keyov zistime 
#SELECT CONSTRAINT_NAME
#FROM information_schema.key_column_usage
#WHERE constraint_schema =  'anketa'
#AND referenced_table_name =  'teacher';

ALTER TABLE Answer DROP FOREIGN KEY FK_DD714F1341807E1D;

UPDATE Answer a
SET a.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = a.teacher_id);

ALTER TABLE Response DROP FOREIGN KEY FK_C70D69AD41807E1D;

UPDATE Response r
SET r.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = r.teacher_id);

ALTER TABLE TeachersSubjects DROP FOREIGN KEY FK_20BF420041807E1D;

UPDATE TeachersSubjects ts
SET ts.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = ts.teacher_id);

ALTER TABLE TeachingAssociation DROP FOREIGN KEY FK_9DACAAB341807E1D;

UPDATE TeachingAssociation ta
SET ta.teacher_id = (SELECT uf.id FROM UserFinal uf WHERE uf.tid = ta.teacher_id);

ALTER TABLE UserFinal DROP COLUMN tid;

SET foreign_key_checks = 0;

DROP TABLE User;

DROP TABLE Teacher;

SET foreign_key_checks = 1;

RENAME TABLE UserFinal TO User;

#INDEX vytvori foreign key automaticky
ALTER TABLE User 
ADD FOREIGN KEY (department_id) 
REFERENCES Department (id);

ALTER TABLE Answer
ADD FOREIGN KEY (teacher_id)
REFERENCES User(id);

ALTER TABLE Response
ADD FOREIGN KEY (teacher_id)
REFERENCES User(id);

ALTER TABLE TeachersSubjects
ADD FOREIGN KEY (teacher_id)
REFERENCES User(id);

ALTER TABLE TeachingAssociation
ADD FOREIGN KEY (teacher_id)
REFERENCES User(id);