## 一个小商店
### 基于Laravel5.4 + PHP7.0，IM基于Swoole + LayIM

### 1. 功能
- 用户管理（注册&amp;登录&amp;重置密码，带邀请码功能）
- 订单提交及处理（目前支持用户主动取消订单（库存实时回滚），带队列邮件通知功能）
- 工单提交及处理（支持上传图片附件，带队列邮件通知功能）
- 活动管理（目前有邀请码生成、红包生成分享及自动充值）
- 宣传页面内容管理（富文本编辑器编辑和保存，redis储存，mysql保存修改记录）
- 在线IM客服（目前支持一对一实时聊天，需要登录）
- 节点库存管理（订单页面实时库存查询，管理页面库存查询、库存操作记录查询，全新入库、库存调增、调减）
- 节点流量查询(用户近7天&近3个月流量使用情况查询)
- 节点管理(添加节点、修改节点信息(自定义节点信息))

### 2. TODO

#### 已上传
- 注册功能模型控制器(MC)
- 登录验证功能MC
- 红包功能（创建、分享、充值）
- IM服务端（支持实时单聊&amp;群聊（一个群））
- 添加邀请码
- 宣传页面内容管理（内容修改&amp;保存）
- 节点库存管理
- 用户主动取消订单功能（库存实时回滚）
- 密码重置
- 7天&3个月流量统计以及相应的直方图展示
- 添加节点、修改节点信息功能(自定义节点信息)
- 工单提交(支持上传附件jpg)

#### 流程图
![FlowChart](https://github.com/TonyStark10006/SSStore/raw/master/flowchart.png)

## A Small Store
### based on Laravel5.4 &amp; PHP7.0, IM module is based on Swoole &amp; LayIM.

### 1. feature
- User Management(register&amp;login&amp;reset password, including generating invitation code feature)
- Order Processing(common user/admin cancel order supported currently(stocks are rolled back in real time), including sending mail notification queue feature)
- Work Order Processing(include uploading image/jepg attachment and sending mail notification queue feature)
- Activities management(generate invitation code, generate lucky money&amp; charge supported currently)
- Content of Introduction Page Management(use rich text editor process and save, saved by redis, modification records are saved by mysql)
- Online IM Customer Services(chat one-to-one in real time, login needed)
- Node Stock Management(query in order page in real time, query surplus stock and log in management page. meanwhile, supporting adding&amp;adjusting stock)
- Node Flow Query(query the used flow in last 7 days&amp; 3 months)
- Node Msg Management(add a node, modify node msg(custom node info))

#### 2. TODO

#### uploaded
- register&amp;login Model&amp;Controller
- lucky money feature
- online IM customer services(one-to-one&amp;group chat(one) supported)
- generate invitation code
- content of introduction page management
- node stock management
- common user&amp;admin cancel order feature(stocks are rolled back in real time)
- reset password
- flow statistics in last 7 days&amp;3 months, histogram display
- add a custom node, modify node msg
- submit a work order(upload a image/jepg attachment supported)