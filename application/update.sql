
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
    `min_amount` float DEFAULT NULL COMMENT '最小金额',
    `max_amount` float DEFAULT NULL COMMENT '最大金额',
    `in_rate` float DEFAULT NULL COMMENT '入款费率',
    `out_rate` float DEFAULT NULL COMMENT '出款费率',
    `in_fiexd_rate` float DEFAULT NULL COMMENT '入款固定费率',
    `out_fiexd_rate` float DEFAULT NULL COMMENT '出款固定费率',
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

-- 会员钱包
create TABLE if not exists fa_member_wallet (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `balance` float Default 0 COMMENT '余额',
    `blocked_balance` float Default 0 COMMENT '冻结余额',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员钱包表';

-- 会员余额变动记录
create TABLE if not exists fa_member_wallet_log (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `amount` float Default 0 COMMENT '变动金额',
    `before_balance` float Default 0 COMMENT '变动前余额',
    `after_balance` float Default 0 COMMENT '变动后余额',
    `type` varchar(255)  NOT NULL COMMENT '类型',
    `remark` varchar(255)  NOT NULL COMMENT '备注',
    `create_time` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员余额变动记录表';

-- 会员冻结列表
create TABLE if not exists fa_member_wallet_freeze (
    `id` int NOT NULL AUTO_INCREMENT,
    `member_id` int NOT NULL COMMENT '会员id',
    `order_no` varchar(255)  NOT NULL COMMENT '业务单号',
    `amount` float Default 0 COMMENT '冻结金额',
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

