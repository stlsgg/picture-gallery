## About
[github url](https://github.com/stlsgg/picture-gallery)

---

Приложение "фото-галерея", с разделением фронт (html, js, css) и бэк (php rest api).
Без базы данных, все хранится в едином json файле.

### Реализованные функции:
1. загрузка изображений
2. хранение информации о изображениях в meta.json
3. api: check, images (все картинки), images/{id} (картинка по id)
4. пагинация
5. drag-and-drop для формы загрузки изображения
6. проверка на дубликат (не даст загрузить изображение)
7. генерация имени (не доверяем именование картинок пользователю)
8. создание thumbnail на основе оригинала
9. добавление watermark на оригинал
10. добавление даты загрузки на thumbnail

### Использованые технологии:
1. frontend:
    - html
    - css (sass)
    - [spectre.css](https://github.com/picturepan2/spectre)
    - pnpm (пакетный менеджер)
    - js (некоторые исходники из [другого моего проекта](https://github.com/stlsgg/coffee-app))

2. backend:
    - php
    - php extension: Gd для обработки изображений

## Setup

### Зависимости:
- git
- pnpm
- docker:
    * engine
    * buildx
    * compose


Также подразумевается, что у вас система GNU/Linux или установлен WSL.
Для работы сайта необходимо прописать в `hosts` домены `gg.ru`, `api.gg.ru` на
`localhost`. Пример на Linux:

```plaintext
# Static table lookup for hostnames.
# See hosts(5) for details.
127.0.0.1        localhost gg.ru api.gg.ru
::1              localhost
```

```bash
git clone https://github.com/stlsgg/picture-gallery.git
cd picture-gallery/frontend
pnpm install
pnpx sass css/main.scss css/main.css
cp node_modules/spectre.css/dist/spectre{.min.css,-icons.min.css} css/
cd ../compose
docker compose up -d
```

После запуска контейнеров сайт будет доступен в браузере по url `gg.ru`. Доступ
к api по url `api.gg.ru`. Доступные endpoints:

```
/images - GET, POST
/images/{id:int} - GET
/check - GET
```
