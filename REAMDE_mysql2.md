```
SELECT 
    filed_a
FROM table_l 
JOIN table_d on table_l.id = table_d.id
WHERE
    table_l.date_end >= DATE('2019-07-01')
    AND table_l.date_end < (DATE('2019-07-01') + INTERVAL 1 month)
    AND table_l.date_end >= table_l.date_start
    AND table_l.flag = 1
    AND table_l.type = "S"
    AND table_d.id_m = 1234
```

| id  | select_type | table   | partitions | type   | possible_keys                             | key            | key_len | ref        | rows  | filtered | Extra                    |
| --- | ----------- | ------- | ---------- | ------ | ----------------------------------------- | -------------- | ------- | ---------- | ----- | -------- | ------------------------ |
| 1   | SIMPLE      | table_l |            | range  | PRIMARY,flag,date_end,type,flag_type_date | flag_type_date | 40      |            | 61708 | 33.33    | Using where; Using index |
| 1   | SIMPLE      | table_d |            | eq_ref | PRIMARY,id_m                              | PRIMARY        | 4       | table_l.id | 1     | 5.00     | Using where              |

- table_l是主表、table_d是副表，总数据量接近1KW
- 其中 flag_type_date 为组合索引  KEY flag_type_date (flag,type,date_end,date_start)
- DATE('2019-07-01')、flag = 1、'S'、1234 这几个参数值都会根据场景而不同
- 请根据上述信息，给出可能的优化办法并说明原因 

#### 根据已知的 explain 分析结果得到的一些信息
  * 查询级别是range 
  * 实际使用到了索引flag_type_date
  * Extra（Using where; Using index），使用了 WHERE 子句进行过滤，并且使用了覆盖索引，避免了回表操作；
#### 优化思路如下
##### 优化1 改变组合索引顺序 
    *   KEY flag_type_date (flag,type,date_end,date_start) 改成 flag_type_date (date_start,date_end,flag,type)
    * ALTER TABLE table_l DROP INDEX flag_type_date;
      CREATE INDEX flag_type_date ON table_l (date_start,date_end, flag, type);
    *  根据查询条件的使用频率和选择性，可以调整索引字段的顺序。由于 flag 和 type 的选择性可能不高，区分度不大，可以考虑将 date_start，date_end 放在索引的前面，这样可以更好地利用范围查询。
#####  优化2 sql 优化 改成子查询
    * 在基于优化1 修改 索引的顺序之后 在增加table_d 表的id_m 索引 
    * CREATE INDEX idx_id_m ON table_d (id_m);
```
SELECT 
          filed_a
      FROM 
          (SELECT * FROM table_l 
           WHERE date_end >= DATE('2019-07-01')
             AND date_end < (DATE('2019-07-01') + INTERVAL 1 month)
             AND date_end >= date_start
             AND flag = 1
             AND type = "S") AS filtered_table_l
      JOIN 
          table_d ON filtered_table_l.id = table_d.id
      WHERE 
          table_d.id_m = 1234;
```
#####  优化3  sql分表把表拆小
```mysql
--  table_l，我们按年份分表
CREATE TABLE table_l_2022 LIKE table_l;
CREATE TABLE table_l_2023 LIKE table_l;
CREATE TABLE table_l_2024 LIKE table_l;

-- 插入数据时根据日期插入到不同的分表中
INSERT INTO table_l_2022 SELECT * FROM table_l WHERE date_end < '2023-01-01';
INSERT INTO table_l_2023 SELECT * FROM table_l WHERE date_end < '2024-01-01';
INSERT INTO table_l_2024 SELECT * FROM table_l WHERE date_end > '2024-01-01';
-- 可以设置表的自增值是以年做起始值，方便其他查询
ALTER TABLE table_l_2022 AUTO_INCREMENT=2022;
ALTER TABLE table_l_2023 AUTO_INCREMENT=2023;
ALTER TABLE table_l_2024 AUTO_INCREMENT=2024;
```
* 通过拆表把表数据量变小，然后在业务代码上限制查询
假如是要用户自己看的历史数据，可以限制只查本年内的，超过限制可以做申请历史查询记录功能，通过后台脚本跑出数据导出excel 发送给用户邮箱查看



第三题

https://www.yuque.com/hongliyuyulvliyuyulvxoxo/hbwglt/isqpae2uzc3g669n?singleDoc# 《无标题画板》