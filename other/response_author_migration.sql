ALTER TABLE Response ADD author_id INT NOT NULL;
UPDATE Response r, User u SET r.author_id = u.id WHERE r.author_login = u.login;
ALTER TABLE Response DROP author_text, DROP author_login;
ALTER TABLE Response ADD CONSTRAINT FK_C70D69ADF675F31B FOREIGN KEY (author_id) REFERENCES User (id);