gini-debade
===========

1. 配置命名为`foo`的队列:
```yml
# debade.yml
queues:
  foo:
    driver: ZMQ
    options:
      addr: ipc:///tmp/foo.ipc   
```

2. 程序调用:
```php
\Gini\Queue::of('foo')->push(['hello'=>'world']);
```

