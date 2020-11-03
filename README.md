# think-addons
ThinkPHP 6 Addons Package

## 鸣谢
本版本来源于原作者<a href='https://github.com/zz-studio/think-addons' target='blank'>https://github.com/zz-studio/think-addons</a>发布的6.0版本修改而来，将在配置文件中的插件和钩子放到数据库中保存，并去除未用到函数及功能。

## 安装
> composer require giteeres/think-addons

## 数据库
### 插件表
```sql
CREATE TABLE `addons` (
  `addonId` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `config` text COMMENT '配置',
  `author` varchar(40) DEFAULT '' COMMENT '作者',
  `version` varchar(20) DEFAULT '' COMMENT '版本号',
  `createTime` datetime NOT NULL COMMENT '安装时间',
  `dataFlag` tinyint(4) DEFAULT '1',
  `isConfig` tinyint(4) DEFAULT '0',
  `updateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`addonId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```
### 钩子表
```sql
CREATE TABLE `hooks` (
  `hookId` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `hookRemarks` text NOT NULL COMMENT '描述',
  `hookType` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型',
  `updateTime` datetime NOT NULL COMMENT '更新时间',
  `addons` text,
  PRIMARY KEY (`hookId`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```