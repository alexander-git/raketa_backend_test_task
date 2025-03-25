create table if not exists categories
(
    id char(36) primary key comment 'UUID категории',
    name varchar(255) not null comment 'Название категории'
) comment 'Категории';

CREATE UNIQUE INDEX categories__name__uniq ON categories (name);


create table if not exists products
(
    id char(36) primary key comment 'UUID товара',
    category_id char(36) not null comment 'Категория товара',
    is_active tinyint default 1  not null comment 'Флаг активности',
    name varchar(255) not null comment 'Название товара',
    description text null comment 'Описание товара',
    thumbnail  varchar(255) null comment 'Ссылка на картинку',
    price DECIMAL(10,2) NOT NULL COMMENT 'Цена'
) comment 'Товары';

ALTER TABLE products ADD CONSTRAINT fk__products__category_id FOREIGN KEY (category_id) REFERENCES categories (id);

create index products__category_id__idx on products (category_id);
