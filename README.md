<p align="center">
<a href="https://packagist.org/packages/yangjisen/quick-passport"><img src="https://img.shields.io/packagist/dt/yangjisen/quick-passport" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/yangjisen/quick-passport"><img src="https://img.shields.io/packagist/v/yangjisen/quick-passport" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/yangjisen/quick-passport"><img src="https://img.shields.io/packagist/l/yangjisen/quick-passport" alt="License"></a>
</p>

## 说明

* [Laravel Passport](https://laravel.com/docs/passport) 是一个简单易用的 OAuth2 服务器和 API 认证包。
* 由于经常需要配置 Passport, 所以写了一个快速安装配置packages.
* 本包只是简单的配置了 Passport, 并简单配置了缓存
  * ``` Laravel\Passport\ClientRepository ``` 
  * ``` Laravel\Passport\TokenRepository ```

## 安装

```
composer require yangjisen/quick-passport
```

## 命令行
``` php artisan passport:quick-install ``` 快速安装配置Passport 相当于执行 ``` php artisan passport:install ``` 和 ``` php artisan passport:env-client ```

``` php artisan passport:env-client ``` 在env文件中根据数据库生成client_id和client_secret
