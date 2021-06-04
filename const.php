<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 04.06.21 10:28
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 04.06.2021
 * Time: 10:28
 *
 * константы - имена переменных сесии
 *
 */

// код пользователя
define('UID', 'crecard_uid');
// признак - грязный долг (надо пересчитывать)
define('DIRTYDOLG', 'crecard_dirty_dolg');
// значение общего долга
define('DOLG', 'crecard_dolg');
// значение остатка (лимит-долг)
define('OSTATOK', 'crecard_ostatok');
// минимальная оплата в дату безпроцентной оплаты
define('SUMMAPAY', 'crecard_summapay');
// дата безпроцентной оплаты
define('DATEPAY', 'crecard_datepay');

// признак платежи-оплаты
define('PAYOFF', 'crecard_payoff');
// признак возможности редактировать
define('CANEDIT', 'crecard_canedit');

// код верификации данных регистрации
define('VERIFY', '6348955746');
// имя сессионной переменной для хранения промежуточных данных
define('DATANAME', 'crecard_RenewData');

// сообщение об ошибке
define('ERRORMESSAGE', 'crecard_error_message');
