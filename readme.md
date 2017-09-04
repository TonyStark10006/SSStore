## 一个小商店
### 基于Laravel + PHP7.0，IM基于Swoole + LayIM

### 1. 功能
- 用户管理（注册&amp;登录，带邀请码功能）
- 订单提交及处理（带队列邮件通知功能）
- 工单提交及处理（带队列邮件通知功能）
- 活动管理（目前有邀请码生成、红包生成分享及自动充值）
- 宣传页面内容管理（富文本编辑器编辑和保存，redis储存）
- 在线IM客服（目前支持一对一实时聊天，需要登录）
- 节点库存管理（订单页面实时库存查询，管理页面库存查询、库存操作记录查询，全新入库、库存调增、调减）

### 2. TODO

#### 已上传
- 注册功能模型控制器(MC)
- 登录验证功能MC
- 红包功能（创建、分享、充值）
- IM服务端（支持实时单聊&amp;群聊（一个群））
- 添加邀请码
- 宣传页面内容管理（内容修改&amp;保存）
- 节点库存管理