
-- 渠道
create TABLE if not  fa_channel (
    `id` int NOT NULL AUTO_INCREMENT,
    `title` varchar(255)  NOT NULL COMMENT '通道名称',
    `code` varchar(255) NOT NULL COMMENT '通道编码',
    `sign` varchar(255)  NOT NULL COMMENT '签名',
    `mch_id` varchar(255)  NOT NULL COMMENT '商户id',
    `mch_key` varchar(255)  NOT NULL COMMENT '商户key',
    `gateway` varchar(255)  NOT NULL COMMENT '网关',
    `is_in` smallint DEFAULT NULL COMMENT '是否开启入款',
    `is_out` smallint DEFAULT NULL COMMENT '是否开启出款',
    `status` smallint DEFAULT NULL COMMENT '状态',
    `min_amount` decimal(10,4) DEFAULT NULL COMMENT '最小金额',
    `max_amount` decimal(10,4) DEFAULT NULL COMMENT '最大金额',
    `in_rate` decimal(10,4) DEFAULT NULL COMMENT '入款费率',
    `out_rate` decimal(10,4) DEFAULT NULL COMMENT '出款费率',
    `in_fiexd_rate` decimal(10,4) DEFAULT NULL COMMENT '入款固定费率',
    `out_fiexd_rate` decimal(10,4) DEFAULT NULL COMMENT '出款固定费率',
    `extra` varchar(500)  DEFAULT NULL COMMENT '额外配置',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 通道
create TABLE if not exists fa_project (
    `id` int NOT NULL AUTO_INCREMENT,
    `title` varchar(255)  NOT NULL COMMENT '项目名称',
    `status` smallint DEFAULT NULL COMMENT '状态',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `fa_project` ADD `area_id` INT NOT NULL COMMENT '地区id' AFTER `status`;

-- 通道渠道关联
create TABLE if not exists fa_project_channel (
    `id` int NOT NULL AUTO_INCREMENT,
    `project_id` int NOT NULL COMMENT '项目id',
    `channel_id` int NOT NULL COMMENT '通道id',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 会员
create TABLE if not exists fa_member (
    `id` int NOT NULL AUTO_INCREMENT,
    `username` varchar(255)  NOT NULL COMMENT '用户名',
    `email` varchar(255)  NOT NULL COMMENT '邮箱',
    `password` varchar(255)  NOT NULL COMMENT '密码',
    `salt` varchar(255)  NOT NULL COMMENT '盐',
    `status` smallint Default 1 COMMENT '状态',
    `api_key` varchar(255)  NOT NULL COMMENT 'api_key',
    `is_sandbox` smallint Default 0 COMMENT '是否沙箱',
    `is_agency` smallint Default 0 COMMENT '是否代理',
    `agency_id` int Default 0 COMMENT '代理id',
    `usdt_address` varchar(255)  default '' COMMENT 'usdt地址',
    `docking_type` tinyint Default 0 COMMENT '对接类型 1:api 0:手动',
    `last_login_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员表';
--- 会员添加ip白名单
ALTER TABLE `fa_member` ADD `ip_white_list` VARCHAR(255) default '' COMMENT 'ip白名单' AFTER `usdt_address`;
ALTER TABLE `fa_member` ADD `area_id` INT NOT NULL COMMENT '地区id' AFTER `ip_white_list`;

-- 会员钱包
create TABLE if not exists fa_member_wallet (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `balance` decimal(10,4) Default 0 COMMENT '余额',
    `blocked_balance` decimal(10,4) Default 0 COMMENT '冻结余额',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员钱包表';

-- 会员余额变动记录
create TABLE if not exists fa_member_wallet_log (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `amount` decimal(10,4) Default 0 COMMENT '变动金额',
    `before_balance` decimal(10,4) Default 0 COMMENT '变动前余额',
    `after_balance` decimal(10,4) Default 0 COMMENT '变动后余额',
    `type` varchar(255)  NOT NULL COMMENT '类型',
    `remark` varchar(255)  NOT NULL COMMENT '备注',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员余额变动记录表';

-- 会员余额变动记录添加业务单号 和 会员id索引
ALTER TABLE `fa_member_wallet_log` ADD `order_no` VARCHAR(255) NOT NULL COMMENT '业务单号' AFTER `member_id`;
ALTER TABLE `fa_member_wallet_log` ADD INDEX `member_id` (`member_id`);
ALTER TABLE `fa_member_wallet_log` ADD INDEX `order_no` (`order_no`);

-- 会员冻结列表
create TABLE if not exists fa_member_wallet_freeze (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `order_no` varchar(255)  NOT NULL COMMENT '业务单号',
    `amount` decimal(10,4) Default 0 COMMENT '冻结金额',
    `remark` varchar(255)  NOT NULL COMMENT '备注',
    `freeze_type` tinyint Default 0 COMMENT '冻结类型',
    `status` smallint Default 0 COMMENT '状态',
    `thaw_time` timestamp  COMMENT '解冻时间',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (id),
    KEY `member_id` (`member_id`),
    KEY `order_no` (`order_no`),
    KEY `create_time` (`create_time`),
    KEY `thaw_time` (`thaw_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员冻结列表';


--- 会员通道费率
create TABLE if not exists fa_member_project_channel (
   `id` int NOT NULL AUTO_INCREMENT,
   `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态',
   `member_id` bigint NOT NULL,
   `project_id` bigint NOT NULL,
   `type` tinyint NOT NULL DEFAULT '1' COMMENT '1代收 2代付',
   `fixed_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '固定成本',
    `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本比例',
    `channel_id` bigint NOT NULL DEFAULT '0' COMMENT '通道id',
    `sub_member_id` int NOT NULL DEFAULT '0' COMMENT '子商户号',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员通道费率';

--- 提款单
create TABLE if not exists fa_withdraw_order (
    `id` int NOT NULL AUTO_INCREMENT,
    `order_no` varchar(255)  NOT NULL COMMENT '订单号',
    `member_id` int NOT NULL COMMENT '会员id',
    `amount` decimal(10,4) Default 0 COMMENT '金额',
    `usdt_amount` decimal(10,4) Default 0 COMMENT 'usdt金额',
    `usdt_address` varchar(255)  NOT NULL COMMENT 'usdt地址',
    `status` smallint Default 0 COMMENT '状态',
    `remark` varchar(255)  NOT NULL COMMENT '备注',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提款单';


--- 代付单
create TABLE if not exists fa_order_in (
    `id` int NOT NULL AUTO_INCREMENT,
    `order_no` varchar(255)  NOT NULL COMMENT '订单号',
    `member_id` int NOT NULL COMMENT '会员id',
    `amount` decimal(10,4) Default 0 COMMENT '金额',
    `true_amount` decimal(10,4) Default 0 COMMENT '实际金额',
    `actual_amount` decimal(10,4) Default 0 COMMENT '实际到账金额',
    `fee_amount` decimal(10,4) Default 0 COMMENT '手续费',
    `channel_fee_amount` decimal(10,4) Default 0 COMMENT '通道手续费',
    `project_id` int NOT NULL COMMENT '项目id',
    `channel_id` int NOT NULL COMMENT '通道id',
    `pay_url` varchar(500) Default '' NOT NULL COMMENT '支付地址',
    `attach` varchar(255) Default '' NOT NULL COMMENT '附加信息',
    `order_ip` varchar(255) Default '' NOT NULL COMMENT '订单ip',
    `error_msg` varchar(255) Default '' NOT NULL COMMENT '消息',
    `e_no` varchar(255) Default '' NOT NULL COMMENT 'E单号',
    `status` smallint Default 0 COMMENT '状态',
    `remark` varchar(255) Default '' NOT NULL COMMENT '备注',
    `pay_success_date` timestamp  COMMENT '支付成功时间',
    `notify_url` varchar(500) Default '' NOT NULL COMMENT '通知地址',
    `notify_status` smallint Default 0 COMMENT '通知状态',
    `notify_count` int Default 0 COMMENT '通知次数',
    `area_id` int Default 0 COMMENT '地区id',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (id),
    KEY `order_no` (`order_no`),
    KEY `member_id` (`member_id`),
    KEY `area_id` (`area_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代付单';

ALTER TABLE `fa_order_in` ADD `member_order_no` VARCHAR(255) NOT NULL COMMENT '会员订单号' AFTER `order_no`;
ALTER TABLE `fa_order_in` ADD `channel_order_no` VARCHAR(255) NOT NULL COMMENT '通道订单号' AFTER `member_order_no`;
-- 添加索引
ALTER TABLE `fa_order_in` ADD INDEX `member_order_no` (`member_order_no`);
ALTER TABLE `fa_order_in` ADD INDEX `channel_order_no` (`channel_order_no`);


--- 地区信息
create TABLE if not exists fa_config_area (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(255)  NOT NULL COMMENT '地区名称',
    `timezone` varchar(255)  NOT NULL COMMENT '时区',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地区信息';

php think crud -t area