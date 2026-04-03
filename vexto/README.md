# VEXTO - Marketplace Inmobiliario Profesional

VEXTO es una plataforma integral diseñada para la venta y alquiler de proyectos inmobiliarios (casas, locales, terrenos), con un enfoque profesional y minimalista.

## Características Principales

### 1. Diferenciación de Usuarios
- **Compañías / Empresas:** Perfiles verificados con insignias especiales y panel de estadísticas avanzado.
- **Usuarios Comunes:** Perfiles particulares para ventas individuales.
- **Límites de Publicación:** Gestión de cuotas según el tipo de cuenta (3 para usuarios, 20 para compañías).

### 2. Mapas Interactivos (Leaflet.js)
- **Ubicación Exacta:** Integración de mapas en la publicación de propiedades para marcar el punto exacto.
- **Visualización:** Mapa interactivo en los detalles de la propiedad para que los clientes vean el entorno.

### 3. Modo Oscuro / Claro Persistente
- **Personalización:** Interruptor de tema en la cabecera que guarda la preferencia del usuario mediante `LocalStorage`.
- **Diseño:** Estética minimalista en Blanco y Negro que se adapta perfectamente a ambos modos.

### 4. Marketplace Estilo E-commerce
- **Filtros Avanzados:** Búsqueda por tipo de propiedad, operación, rango de precio y ubicación.
- **Detalles Completos:** Información técnica detallada (m², habitaciones, baños, descripción completa).
- **Sistema de Citas:** Módulo para agendar visitas directamente con el vendedor.
- **Favoritos:** Sección para guardar y gestionar propiedades de interés.

## Estructura del Proyecto
- `/assets/css/`: Estilos centralizados con variables CSS.
- `/assets/js/`: Lógica de mapas, temas y animaciones.
- `/includes/`: Componentes reutilizables (header, db_connect).
- `/uploads/`: Almacenamiento de imágenes de propiedades.
- `/views/`: (Opcional) Para futuras expansiones de plantillas.

## Instrucciones de Instalación (XAMPP)

1. **Copiar archivos:** Extrae el contenido del ZIP en `C:\xampp\htdocs\vexto`.
2. **Base de Datos:**
   - Inicia Apache y MySQL en XAMPP.
   - Ve a `http://localhost/phpmyadmin`.
   - Crea la base de datos `vexto_db`.
   - Importa el archivo `database.sql` incluido.
3. **Acceso:** Abre `http://localhost/vexto` en tu navegador.

## Tecnologías Utilizadas
- **Backend:** PHP 7.4+ / MySQL.
- **Frontend:** HTML5, CSS3 (Variables), JavaScript (ES6).
- **Librerías:** Anime.js (Animaciones), Leaflet.js (Mapas), FontAwesome (Iconos).
