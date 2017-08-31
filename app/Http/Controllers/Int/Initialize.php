<?php

namespace App\Http\Controllers\Int;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Initialize extends Controller
{

    /*
     * 适用于mysql5.6 5.7版本
     * */
    public function createDBTable()
    {
        $sql['member'] = '
        CREATE TABLE IF NOT EXISTS member (
        user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        permission VARCHAR(10),
        username VARCHAR(30) NOT NULL,
        password VARCHAR(64) NOT NULL,
        user_group VARCHAR(30) NOT NULL,
        remark TEXT,
        reg_date TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['createAdmin'] = '
        INSERT INTO `member` (permission, username, password, user_group, remark) 
        VALUES (1, \'admin\', \'8a2a523b38559c1b7df1a6d47b9bdf2c\', \'ultimate\', \'默认管理员账号\')';

        $sql['nodeList'] = '
        CREATE TABLE IF NOT EXISTS node_list (
        zone_id smallint(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        zone_name varchar(20) NOT NULL,
        description text,
        price int(5) NOT NULL,
        remark tinytext,
        update_time TIMESTAMP NOT NULL
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['order'] = '
        CREATE TABLE IF NOT EXISTS `order` (
        order_id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(10) UNSIGNED NOT NULL,
        order_no varchar(30) NOT NULL,
        zone_id SMALLINT UNSIGNED NOT NULL,
        zone_name varchar(20) NOT NULL,
        period tinyint(4) UNSIGNED NOT NULL,
        total_price int(10) NOT NULL,
        password VARCHAR(255) NOT NULL,
        pay_status tinyint(4) UNSIGNED NOT NULL,
        coupon_code varchar(30),
        coupon_discount varchar(30),
        remark tinytext,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['usageStatus'] = '
        CREATE TABLE IF NOT EXISTS usage_status (
        id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(10) UNSIGNED NOT NULL,
        username VARCHAR(30) NOT NULL,
        zone_name varchar(20) NOT NULL,
        valid_time varchar(255) NOT NULL,
        data_used text,
        status_mark tinyint(4) UNSIGNED NOT NULL,
        remark tinytext,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['lmList'] = '
        CREATE TABLE IF NOT EXISTS lm_list (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lm_id varchar(30) NOT NULL UNIQUE,
        total_period VARCHAR(255) NOT NULL,
        remain_period VARCHAR(255) NOT NULL,
        min_period VARCHAR(255),
        total_number INT UNSIGNED NOT NULL,
        remain_number INT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['lmListFetched'] = '
        CREATE TABLE IF NOT EXISTS lm_list_fetched (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lm_id varchar(30) NOT NULL UNIQUE,
        user_id INT UNSIGNED NOT NULL,
        period_fetched INT UNSIGNED NOT NULL,
        zone_fetched VARCHAR(20) NOT NULL,
        fetch_status TINYINT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['introduction'] = '
        CREATE TABLE IF NOT EXISTS `introduction` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `author_user_id` INT UNSIGNED NOT NULL,
          `title` text,
          `type` TINYINT UNSIGNED NOT NULL,
          `content` longtext NOT NULL,
          `create_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
          `update_time` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';

        $sql['invCode'] = '
        CREATE TABLE IF NOT EXISTS inv_code (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        inv_code varchar(30) NOT NULL UNIQUE,
        valid_times SMALLINT UNSIGNED NOT NULL,
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        update_time TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' ON UPDATE CURRENT_TIMESTAMP
        )ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $sql['stockLog'] = '
        CREATE TABLE IF NOT EXISTS stock_log (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        order_no varchar(30) NOT NULL,
        zone_id TINYINT UNSIGNED NOT NULL,
        zone_name varchar(20) NOT NULL,
        period TINYINT UNSIGNED NOT NULL,
        remain_period INT UNSIGNED NOT NULL,
        buyer VARCHAR(30) NOT NULL,
        remark VARCHAR(30),
        create_time TIMESTAMP NOT NULL
        )DEFAULT charset=utf8 ENGINE=INNODB;';

        $sql['triggerStockLog'] = '
        delimiter  $
        CREATE TRIGGER STOCK_LOG
        AFTER INSERT ON `order`
        FOR EACH ROW
        BEGIN
        DECLARE RP INT;
        UPDATE stock_log SET remain_period = remain_period - new.period WHERE zone_name = new.zone_name AND remark = \'新增库存\' ORDER BY create_time DESC LIMIT 1 ;
        SET RP = (SELECT remain_period FROM stock_log WHERE remark = \'新增库存\' ORDER BY create_time DESC LIMIT 1);
        INSERT stock_log (zone_id, zone_name, period, order_no, buyer, remain_period) VALUES (new.zone_id, new.zone_name, new.period, new.order_no, new.user_id, RP);
        END $
        DELIMITER ;';

        foreach ($sql as $key => $go) {
            DB::statement($go);
        }

        unset($sql);

        return '数据表创建成功，默认管理员账号为admin，密码nimda';
    }
}
