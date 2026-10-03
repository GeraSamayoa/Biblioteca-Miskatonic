# Biblioteca Miskatonic — Proyecto I (Desarrollo Web)

Aplicación web en **PHP + Bootstrap 5** para administrar libros, préstamos y devoluciones.
La información se guarda en **archivos de texto (.txt)** separados por `|` dentro de `data/`, sin base de datos.

## Cómo ejecutarlo

**Opción A — servidor integrado de PHP** (desde la carpeta del proyecto, en la terminal de VS Code):

```bash
php -S localhost:8000
```

Luego abre <http://localhost:8000>.

**Opción B — XAMPP:** copia la carpeta a `C:\xampp\htdocs\biblioteca` y abre <http://localhost/biblioteca>.

> Requiere PHP 8.0 o superior.

## Estructura

```
biblioteca/
├── index.php            Inicio (resumen y préstamos vencidos)
├── libros.php           Listado, búsqueda y eliminación de libros
├── libro_form.php       Registrar / modificar libro
├── prestamos.php        Registrar préstamos
├── devoluciones.php     Registrar devoluciones
├── consultas.php        Préstamos por estado (Activo, Vencido, Devuelto)
├── config.php           Configuración y carga de la lógica
├── includes/            Partes reutilizables (header, menú, mensajes, footer)
├── lib/                 Lógica: archivos, libros, préstamos, utilidades
├── data/                Persistencia: libros.txt y prestamos.txt
└── assets/              CSS, JS y Bootstrap local
```

## Reglas implementadas

- Validación de campos obligatorios, códigos, años y cantidades (en el servidor).
- No se presta un libro inexistente, inactivo o sin ejemplares disponibles.
- Al prestar se descuenta un ejemplar; al devolver se suma.
- No se puede devolver un préstamo ya devuelto.
- No se puede eliminar un libro con préstamo activo (se confirma antes de eliminar).
- Un préstamo **Vencido** es uno activo cuya fecha prevista ya pasó.
- Al editar un libro no se permite dejar menos ejemplares que los que están prestados.
