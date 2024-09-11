# PHP ilə Custom Query Builder

Bu layihə PHP-də dinamik SQL sorğularını proqramlaşdırma yolu ilə yaratmağa imkan verən xüsusi bir Query Builder sinfini nümayiş etdirir. Obyekt yönümlü proqramlaşdırma (OOP) prinsiplərinə əsaslanır, ümumi SQL sorğuları üçün metodlar və `SUM`, `AVG`, `COUNT` kimi aqreqasiya funksiyalarını dəstəkləyir.

## Xüsusiyyətlər

- SQL sorğularının dinamik qurulması
- `SELECT`, `WHERE`, `OR WHERE`, `IN`, `NOT IN`, `LIKE`, `NOT LIKE`, `BETWEEN`, `NOT BETWEEN` dəstəyi
- Aqreqasiya funksiyaları: `SUM`, `AVG`, `COUNT`
- OOP prinsiplərinə uyğun kod strukturu

## Quraşdırma

Layihəni quraşdırmaq üçün aşağıdakı addımları izləyin:

1. Layihəni klonlayın:
   ```bash
   git clone https://github.com/RasimAghayev/php-query-builder.git
   ```

2. Composer vasitəsilə asılılıqları quraşdırın:
   ```bash
   composer install
   ```

3. Serveri işə salmaq:
   ```sh
   php -S localhost:3000 index.php
   ```

## Quraşdırma

   ```php
   <?php
   require 'vendor/autoload.php';

   use App\Core\QueryBuilder;

   $queryBuilder=null;
      
   $queryBuilder = new QueryBuilder();
   $selectColumnWhereLikeAndQuery = $queryBuilder
      ->table('product')
      ->select('id', 'name', 'price')
      ->where('status', '=', 1)
      ->orWhere(function ($query) {
         $query->where('quantity', '>', 0)
               ->where('amount', '>', 0);
      })
      ->whereIn('customer', [13, 135, 168])
      ->whereNotIn('payer', [13, 135, 168])
      ->whereLike('name', '%Ras%')
      ->toSql();
   echo $selectColumnWhereLikeAndQuery . '<br/>';
   $queryBuilder=null;

   ```

   Nəticə
```sql
   SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%';
   ```


## Testlər

Testlərin yoxlanılması:
```sh
vendor/bin/phpunit
```
