# Changelog

## [1.1.2](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/compare/v1.1.1...v1.1.2) (2026-06-17)


### Bug Fixes

* auto-sync ps_module version when PHP file is newer than DB ([dd0d54c](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/dd0d54c9a5849b8c5ade66452265312d3b1f532d))
* store update nonce in DB to bypass OPcache mismatch ([e032f8c](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/e032f8c565cf924f0d27cda2b6a877f301104d44))

## [1.1.1](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/compare/v1.1.0...v1.1.1) (2026-06-17)


### Bug Fixes

* replace Context-based auth with nonce in update endpoints ([747c94d](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/747c94d5e075ef73ba64293bf2399b65f47c863c))
* skip upgrade/php/ during file copy and update ps_module version ([4e0bb96](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/4e0bb969cd5f6f044591bbba551eac8e1792dc12))

## [1.1.0](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/compare/v1.0.0...v1.1.0) (2026-06-17)


### Features

* add auto-update system with BO notification ([6033715](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/60337159021d692dc573251dc2dea9195c4ab5a1))
* initial commit - pb_cronjobs module v1.0.0 ([da09cf1](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/da09cf1f7f79c03197c9dafa7e17a521432ad30a))


### Bug Fixes

* show installed version on config page, fix getInstalledVersion recursion ([9838410](https://github.com/Thierry-Pimentbleu/Cronjobs-Prestashop/commit/983841021c3e73eeddfff77e935b7bd95160d8c8))
