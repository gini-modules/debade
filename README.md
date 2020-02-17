gini-debade
===========

需要courier.yml的callback增加step=N-1的机制，才能正常使用database的driver
        $step= $_GET['step']; // 2-1: 有两个callback，这是第一个
        $hash = $data['debade::hash']; // 通过这个hash可以获取到data
        $debade = $data['debade::key']; // driver是database的debade.yml配置
        $debade = \Gini\DeBaDe\Queue::of($data['debade::key']);
        if (!$debade->scribe($hash, $step)) return; // 标记，开始执行2-1的逻辑
			$data = $debade->getData($hash);
			error_log(J(['phizz', $data]));
        $debade->ack($hash, $step); // 逻辑成功执行
        $debade->nack($hash, $step); // 逻辑执行失败，等待下次执行。TODO 目前，下次执行的CLI没有写

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

