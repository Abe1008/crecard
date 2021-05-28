create table logs
(
  dat timestamp default CURRENT_TIMESTAMP null comment 'время регистрации',
  uid int                                 null comment 'код пользователя',
  ip  varchar(32)                         null comment 'IP адрес пользователя',
  url varchar(255)                        null comment 'адрес перехода после регистрации'
)  comment 'лог регистрации' engine = MyISAM ;

create table p_files
(
  ifile     int auto_increment comment 'код файла' primary key,
  file_name varchar(255)          null comment 'имя файла документа',
  file_type varchar(255)          null comment 'тип файла документа',
  file_size int       default 0   null comment 'размер файла документа',
  file_hash varchar(255)          null comment 'хэш содержимого файла',
  wdat      timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP comment 'время обновления'
) comment 'файлы приложений' engine = MyISAM;

create table pays
(
  id     int auto_increment comment 'индекс платежа' not null primary key,
  uid    int                      not null comment 'индекс пользователя',
  dat    date                     not null comment 'дата платежа',
  sm     double default 0         not null comment 'сумма платежа',
  payoff int    default 0         not null comment 'признак погашения долга',
  prim   varchar(255)             null     comment 'примечание',
  ifile  int                      null     comment 'файл вложения (например скан чека)',
  wdat   timestamp default CURRENT_TIMESTAMP not null comment 'время создания записи'
)  comment 'платежи пользователей' engine = MyISAM charset = utf8;

create table tmp_tabl
(
  uid  int                        not null comment 'индекс пользователя',
  dat  date                       not null comment 'дата платежа',
  sm   double                     not null comment 'сумма платежа',
  ost  double                     not null comment 'остаток от платежа',
  dato date                       not null comment 'расчетная дата оплаты без процентов'
)  comment 'временная таблица для расчетов' engine = MyISAM;

create table users
(
  uid       int auto_increment comment 'индекс пользователя'  primary key,
  email     varchar(255)                        not null comment 'идентификатор - электронная почта',
  pwd       varchar(255)                        not null comment 'пароль',
  lim       double    default 0                 not null comment 'кредитный лимит',
  rday      int       default 30                not null comment 'расчетный день (начало нового расчетного периода)',
  grace     int       default 25                not null comment 'кол-во льготных дней после даты выписки',
  wdat      timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP comment 'время создания (модификации)'
)  comment 'пользователи' engine = MyISAM;
