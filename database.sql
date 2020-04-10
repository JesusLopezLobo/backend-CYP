CREATE DATABASE IF NOT EXISTS cortesyposes;
USE cortesyposes;

CREATE TABLE users(
    id  int(255) auto_increment not null,
    role    varchar(20),
    name    varchar(255),
    surname varchar(255),
    passw   varchar(255),
    created_at  datetime DEFAULT NULL,
    updated_at  datetime DEFAULT NULL,
    remember_token varchar(255),
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE videos(
    id int(255) auto_increment not null,
    user_id int(255) not null,
    title   varchar(255),
    description text,
    status varchar(200),
    image varchar(255),
    video_path varchar(255),
    created_at datetime,
    updated_at datetime,
    CONSTRAINT pk_videos PRIMARY KEY(id),
    CONSTRAINT fk_videos_users FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

CREATE TABLE poesias(
    id int(255) auto_increment not null,
    user_id int(255) not null,
    title   varchar(255),
    description text,
    status varchar(200),
    image varchar(255),
    created_at datetime,
    updated_at datetime,
    CONSTRAINT pk_poesias PRIMARY KEY(id),
    CONSTRAINT fk_poesias_users FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

CREATE TABLE comments_poesias(
    id  int(255) auto_increment not null,
    user_id int(255) not null,
    poesia_id   int(255) not null,
    body text,
    created_at datetime,
    updated_at datetime,
    CONSTRAINT pk_comment PRIMARY KEY(id),
    CONSTRAINT fk_comment_poesias_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_comment_poesias FOREIGN KEY(poesia_id) REFERENCES poesias(id)

)ENGINE=InnoDb;

CREATE TABLE comments_videos(
    id  int(255) auto_increment not null,
    user_id int(255) not null,
    video_id int(255) not null,
    body text,
    created_at datetime,
    updated_at datetime,
    CONSTRAINT pk_comment PRIMARY KEY(id),
    CONSTRAINT fk_comment_video FOREIGN KEY(video_id) REFERENCES videos(id),
    CONSTRAINT fk_comment_videos_user FOREIGN KEY(user_id) REFERENCES users(id)

)ENGINE=InnoDb;