ALTER TABLE `ocean_ads`
MODIFY COLUMN `budget`  int NULL DEFAULT 0 COMMENT '预算' AFTER `budget_mode`,
MODIFY COLUMN `bid`  int NULL DEFAULT 0 COMMENT '点击出价/展示出价，当pricing为\"CPC\"、\"CPM\"、\"CPV\"出价方式时有值' AFTER `pricing`,
MODIFY COLUMN `cpa_bid`  int NULL DEFAULT 0 COMMENT '目标转化出价/预期成本， 当pricing为\"OCPM\"、\"OCPC\"、\"CPA\"出价方式时有值' AFTER `bid`,
MODIFY COLUMN `deep_cpabid`  int NULL DEFAULT 0 COMMENT '深度优化出价' AFTER `deep_bid_type`;

