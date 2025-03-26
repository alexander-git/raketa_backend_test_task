### .gitignore

composer.lock не должен присутствовать в .gitignore


### migrations/schema.init.sql

1. Используется одновременно id и uuid. Нужно им выбрать что-то одно.
   Имя для поля лучше оставить id, даже если храниться в нём будет uuid.
2. Тип varchar(255) для uuid не самый оптимальный.
   Можно использовать char(36) либо char(16) - в зависимости от того, как будет храниться uuid.
3. Поле name.
    - Комментарий "Тип услуги", хотя поле относится к таблице хранящей товары. Нужно
      поменять на "Название товара"
    - Поле типа text не может в MySQL иметь значение по умолчанию.
    - У товаров вряд ли бывают очень длинные названия, поэтом вместо text нужно
      использовать varchar(255).
4. Поле price. Цену хранить в виде float неправильно потому что тип не гарантирует точность.
   Нужно использовать или BIGINT(и хранить в копейках) или DECIMAL(10,2).
5. Для уменьшения избыточности категории лучше вынести в отдельную таблицу, а в таблице products
   хранить внешний ключ.
6.
    ```
    create index is_active_idx on products (is_active);
    ``` 
Если в базе данных будет мало активных товаров по сравнению
с их общим количеством, то такой индекс имеет смысл. Хотя и в этом случае можно добавить в индекс столбец category:
    ```
    create index products__is_active__category_id__idx on products (is_active, category_id);
    ```
Если же значительная часть товаров активна(как это обычно и бывает), то индекс не будет использоваться в запросе который 
осуществляется в приложении. Тогда можно сделать индекс только по категории. На практике смотреть будет или нет использоваться индекс
с помощью explain.


### Все контроллеры

1. В контроллерах обращаться напрямую к репозиториям не очень хорошая практика. Для этого должны
   использоваться UseCase/Service/Command/Query/и т.д
2. Вооще в случае использования репозитория где-либо в конструкторе нужно использовать
   интерфейс, а не класс напрямую.
3. Почему действия в контроллерах называется get? Лучше использовать addToCart, getCart,
   getProducts или __invoke()/perform/run/и т.д.
4.
    ```
    ->withHeader('Content-Type', 'application/json; charset=utf-8') 
    ```
   Не нужно, так как это должно быть поведением по умолчанию в классе JsonResponse.


### src/Controller/AddToCartController.php

1.
    ```
     $cart = $this->cartManager->getCart();
     $cart->addItem(new CartItem(
         Uuid::uuid4()->toString(),
         $product->getUuid(),
         $product->getPrice(),
         $rawRequest['quantity'],
     ));
   ```
   CartManager и те классы которые он используется устроены так, что
   если корзина раньше не существовала, то метод  getCart вернёт false и здесь произойдёт ошибка.
2.
    ```
    [
        'status' => 'success',
        'cart' => $this->cartView->toArray($cart)
    ],
   ```
   status в json не нужен. При успешном результате достаточно указать в ответе status code 200.

4. Товар добавляется в корзину, на при этом дальше она нигде не сохраняется и действие в итоге
   ничего не сделает.


### src/Controller/GetCartController.php

1.
    ```
    public CartView $cartView,
    public CartManager $cartManager
    ```
   Должны быть объявлены как private.
2. $request нигде не используется.
3. В случе если корзина найдена в ответе всё равно будет статус 404, поскольку в else
   отсутвует return и код после if меняет $response.


### src/Controller/GetProductsController.php

1. Опечатка - productsVew
2. Название не отражает того, что делает контроллер.
   Лучше названить GetActiveProductsByCategoryController.
3. Имя категории надо брать из URI, а не из тела запроса.


### src/Controller/JsonResponse.php

1. Не понятно почему понадобилась своя версия ResponseInterface в которой не реализован
   ни один метод, если существует множество готовых решений.


### src/Domain/Cart.php

1. Перенести в пространство имён Raketa\BackendTestTask\Domain\Entity;
2. Непонятно зачем хранить в корзине paymentMethod. Ведь обычно способ оплаты выбирается
   только при оформлении заказа.
3. В коде приложения отсутсвует какая-либо логика по извлечению Customer.
   Не нужно хранить в корзине полную информацию о пользователе, достаточно будет той, по которой
   пользователя можно найти.
4.
    ```    
    public function __construct(
        readonly private string $uuid,
        readonly private Customer $customer,
        readonly private string $paymentMethod,
        private array $items,
    ) {
    }
   ```
Для $items указать тип с помощью PHPDoc.
5.
    ```
    public function addItem(CartItem $item): void
    {
        $this->items[] = $item;
    }
   ```
Метод не проверяет есть ли уже в корзине такой товар.


### src/Domain/CartItem.php

1. Для чего в CartItem присутствует $uuid если поиск по нему нигде не происходит?
2. Свойства должны быть либо private, либо, так как класс readonly, не иметь геттеров.


### src/Domain/Customer.php

1. Если бы Customer использовался в коде, то тип поля $middleName нужно сделать ?string, так как
   бывают люди без отчества. Да и вообще использование названия middleName для отчества
   может путать, так  отчество это patronymic, а middle name в России встречается не часто.


### src/Infrastructure/Connector.php

1. ConnectorException выбрасывается в методах класса но в коде нигде не обрабатывается.
2. Connector должен быть просто классаом упрощающим доступ к Redis. Он не должен знать что-либо о
   корзине.
3.
    ```
   private Redis $redis;
   
    public function __construct($redis)
    {
        return $this->redis = $redis;
    }
   ```
Можно заменить на объявление свойств в  констуркторе - private readonly Redis $redis.

4.
    ```
    public function get(Cart $key)
    {
        try {
            return unserialize($this->redis->get($key));
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }
   ```
    - $key должен быть типа string, а не Cart
    -  Если в редисе ключа нет то unserialize($this->redis->get($key)) вернёт false, что может быть
       не очень удобно.
    -  Сообщение "Connector error" не несёт никакой полезной информации,
       так как по типу исключения уже понятно, что оно свзано с Connector.
    - Не указан тип возврщаемого значения.
5.
    ```
    public function set(string $key, Cart $value)
    {
        try {
            $this->redis->setex($key, 24 * 60 * 60, serialize($value));
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }
   ```
   Не указан тип возвращаемого значения.
6.
    ```
    public function has($key): bool
    {
        return $this->redis->exists($key);
    }
   ```
    - Исключение RedisException не обрабатывается.
    - Не указан тип для параметра $key.
    - Метод нигде не используется. Нужно удалить.


### src/Infrastructure/ConnectorException.php

1. Зачем реализовывать Throwable есть готовый класс Exception и можно
   унаследоваться от него?


### src/Infrastructure/ConnectorFacade.php
1.
   ```
   public $connector;
   ```
    - Не объявлен тип
    - Так как фасад должен скрывать то к чему мы обращаемся, то свойтво не должно
      быть public, геттера у него тоже быть не должно. Всё обращение к Connector
      должно идти посредством методов фасада.
    - И поскольку назначение класса ConnectorFacade создать объект Connector, то вообще это не
      фасад, а фабрика. В этом случае нет необходимости хранить Connector внутри класса.
2.
    ```
    public string $host;
    public int $port = 6379;
    public ?string $password = null;
    public ?int $dbindex = null;
   
    public $connector;
   
    public function __construct($host, $port, $password, $dbindex)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->dbindex = $dbindex;
    }
   ```
    -  Не указаны типы для $host, $port, $password, $dbindex. Так как все значения для них
       передаются в конструкторе, то значения по умолчанию в теле класса
       никогда не будут использоваться.
    - Лучше исользовать объявление в конструкторе и сделать свойства private.
    - $password не может быть null. Либо строка либо массив.
    - $dbindex не может быть null. Значением по умолчанию может быть 0.
    - В конструкторе не объявлен логгер, который нужен при обработке исключений.

3.
    ```
    protected function build(): void
    {
        $redis = new Redis();
   
        try {
            $isConnected = $redis->isConnected();
            if (! $isConnected && $redis->ping('Pong')) {
                $isConnected = $redis->connect(
                    $this->host,
                    $this->port,
                );
            }
        } catch (RedisException) {
        }
   
        if ($isConnected) {
            $redis->auth($this->password);
            $redis->select($this->dbindex);
            $this->connector = new Connector($redis);
        }
    }
   ```
    - Проверка подключения $redis->isConnected() идёт до того как подключение осуществленно.
    - $redis->ping('Pong') в случее успеха вернёт строку 'Pong' использовать её в логическом
      выражении возможно, но лучше использовать явно $redis->ping('Pong') === 'Pong'
    - Исключение RedisException никак не обрабатывается.
    - Успешность действий $redis->auth($this->password) и $redis->select($this->dbindex)
      не проверяется
    - Исключения которые могут возникнуть в  $redis->auth($this->password) и
      $redis->select($this->dbindex) не перехватываются.

### src/Repository/Entity/Product.php

1. Бизнес-сущности должны находится на слое Domain. Перенести в пространство имён
   Raketa\BackendTestTask\Domain\Entity;
2.
    ```
    public function __construct(
        private int $id,
        private string $uuid,
        private bool $isActive,
        private string $category,
        private string $name,
        private string $description,
        private string $thumbnail,
        private float $price,
    ) {
    }
   ```
    - Используется одновременно $id и $uuid. Нужно выбрать что-то одно.
    - В качестве типа использовать готовый UUID - Ramsey\Uuid\UuidInterface;
    - description и thumbnail могут быть NULL в БД, поэтому и у полей
      $description и $thumbnail  тип должен быть ?string
    - Цену хранить в float неправильно, так как нет гарантий точности.
      Можно использовать int и хранить в копейках или использовать ValueObject.

### src/Repository/ProductRepository.php

1. Перенести в пространство имён
   Infrastructure Raketa\BackendTestTask\Infrastructure\Repository

2.
    ```
    public function __construct(Connection $connection) 
    ```
   Можно использовать объявление свойства в конструкторе и добавть к классу readonly.
3.
     ```   
     public function getByUuid(string $uuid): Product
     {
         $row = $this->connection->fetchOne(
             "SELECT * FROM products WHERE uuid = " . $uuid,
         );
    
         if (empty($row)) {
             throw new Exception('Product not found');
         }
    
         return $this->make($row);
     }
    ```
    - Exception не импортирован в текущее простраснство имён. Нужно использовать "\Exception" или "use Exception".
    - Нужно создать отдельное исключение на случай ели продукт не найден.
    - Лучше проверять на false явно, а не использовать empty($row).
    - Для ивзлечения одной строки нужно использовать fetchAssociative, а не fetchOne.
    - Для защиты от SQL-инъекций нужно использовать prepared statements.
      Для этого нужно передать значение  во второй параметр функции fetchAssociative
4.
     ```   
     public function getByCategory(string $category): array
     {
         return array_map(
             static fn (array $row): Product => $this->make($row),
             $this->connection->fetchAllAssociative(
                 "SELECT id FROM products WHERE is_active = 1 AND category = " . $category,
             )
         );
     }
    ```
    - Название getByCategory не отражает того, что делает метод.
      Лучше назвать getActiveProductsByCategoryName или findActiveProductsByCategoryName
    - static fn (array $row): Product => $this->make($row),
      Используетя $this поэтому стрелочная функция не должна быть static.
    - Выбирается только id товара, хотя нам нужны все данные
    - Для защиты от SQL-инъекций нужно использовать prepared statements. Для этого нужер передать значение
      во второй параметр функции fetchAllAssociative
    - Нужно через PHPDoc указать какие элементы содержит возвращающийся массив.

5.  ```
    public function make(array $row): Product
    ```
    Метод используется только внутри класса, поэтму должен быть private.

### src/Repository/CartManager.php

1. Если реаилизовать нужно было REST API, то использовать сессии неправильно, так как
   REST API должен быть Stateless. Но даже если отбростить это, какой-либо способ получения
   информации о том к какому пользователю относиться корзина не должен быть внутри
   этого класса, а должен приходить извне.
2. Почему класс называется CartManager если он лежит в папке Repository и по существу и
   является  репозиторием?
3. Наследовать фасад неправильно. Нужно использовать агрегацию.
4.
    ```
    public $logger;
    ```
    - Не указан тип.
    - Должен быть private.

5.
   ```
   parent::__construct($host, $port, $password, 1);
   ```
   Непонятно почему dbindex жётско задан в коде, а не вынесен в параметр конструктора.
6.
    ```
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    ```
   Не указан тип возвращаемого значения void.
7.
    ```
    /**
     * @inheritdoc
     */
    public function saveCart(Cart $cart)
    {
        try {
            $this->connector->set($cart, session_id());
        } catch (Exception $e) {
            $this->logger->error('Error');
        }
    }
   ```
    - @inheritdoc лишний, так как метода нет в базовом классе и неоткуда взять PHPDoc.
    - Не указан тип возвращаемого значения.
    - session_id() вызывается без вызова session_start().
    - Перепутан порядок аргументов $cart, session_id().
    - В логгер записывается абсолютно неинформативное сообщение.
8.
    ```
    /**
     * @return ?Cart
     */
    public function getCart()
    {
        try {
            return $this->connector->get(session_id());
        } catch (Exception $e) {
            $this->logger->error('Error');
        }
   
        return new Cart(session_id(), []);
    }
   ```
    - Вместо @return лучше укзать тип.
    - null не может быть возвращён, при этом функция может вернуть false, что
      не очень удобно.
    - Конструктору Cart при создании передаются не все аргументы.

### src/View/CartView.php

1. Информация о покупателе добавляется к выводу содержимого корзины. При этом в коде Customer нигде не создаётся и
   не заполняется. Также непонятно зачем корзина вообще хранит подробную информацию о пользователе и методе оплаты.
2.
   ```
       public function __construct(
        private ProductRepository $productRepository
    ) {
    }
   ```
   Репозитории не должны использоваться во View.
3.
    ```
    $total = 0;
        $data['items'] = [];
        foreach ($cart->getItems() as $item) {
            $total += $item->getPrice() * $item->getQuantity();
   ```
   Логику вычисления суммарно цены нужно вынести в классы Cart и CartItem.
4.
    ```
   $product = $this->productRepository->getByUuid($item->getProductUuid());
   ```
   Метод getByUuid может выбростиь исключение но оно нигде не обрабатывается.
5. Информация о цене за товар записывается в результат два раза - при использовании $item->getPrice() и
   $product->getPrice().
6.
    ```
    $total += $item->getPrice() * $item->getQuantity();
    $product = $this->productRepository->getByUuid($item->getProductUuid());
   
    $data['items'][] = [
        'uuid' => $item->getUuid(),
        'price' => $item->getPrice(),
        'total' => $total,
   ```
   Записывается суммарная цена за текущий товар + суммарная цена за товары обработанный на предыдущих шагах.
   Хотя скорее всего нужна была суммарная цена за одну позицию корзины.

### src/View/ProductsView.php

1.
    ```
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }
    ```
   Репозитории не должны использоваться во View.
2.
   ```
   public function toArray(string $category): array
   ```
   Из назваения метода трудно понять, что возвращается список активных продуктов относящийся к
   определённой категории.
3. В результат не записывается название товара.

## Примечания по исправлению

1. В задании не сказано какого вида должны быть эндпоинты у методов корзины. Поэтому сделал для чего-то
   вроде "GET /cart" и "POST  /cart/add-item". Т.е. без id корзины в uri. Если бы тут предполагалось, что это это
   REST API, то для работы с такими uri необходимо присылать данные нужные для идентификации пользователя 
   при каждом запросе - для демонстрации такого подхода сделан пример с Basic-авторизацией.
2. CartRepository зависит от RedisConnector. Предполагается, что его внедрение с помощью метода create
   RedisConnectorFactory будет настроено при конфигурировании контейнера.