1. 安装 phalcon
2. ``compser install``
3. 运行定时器服务,``php command/timer_process.php``
4. mysql数据库添加表
``CREATE TABLE `timer_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(64) DEFAULT NULL,
  `method` varchar(64) DEFAULT NULL,
  `params` varchar(256) DEFAULT NULL,
  `exec_time` int(11) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;``

5.运行test