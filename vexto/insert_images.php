<?php
require_once 'db_connect.php';

// Array de propiedades con sus archivos de imagen
$properties = [
    [
        'id' => 1,
        'titulo' => 'Lujosa Casa en Playa Dorada',
        'imagen' => '/home/ubuntu/vexto/property1.jpg'
    ],
    [
        'id' => 2,
        'titulo' => 'Apartamento Moderno en Zona Colonial',
        'imagen' => '/home/ubuntu/vexto/property2.jpg'
    ],
    [
        'id' => 3,
        'titulo' => 'Terreno Comercial en Avenida Principal',
        'imagen' => '/home/ubuntu/vexto/property3.jpg'
    ],
    [
        'id' => 4,
        'titulo' => 'Local Comercial en Centro Comercial Premium',
        'imagen' => '/home/ubuntu/vexto/property4.jpg'
    ]
];

foreach ($properties as $prop) {
    if (file_exists($prop['imagen'])) {
        $imagen_data = file_get_contents($prop['imagen']);
        $imagen_tipo = 'image/jpeg';
        
        $stmt = $pdo->prepare("UPDATE properties SET imagen = ?, imagen_tipo = ? WHERE id = ? AND user_id = 1");
        $stmt->execute([$imagen_data, $imagen_tipo, $prop['id']]);
        
        echo "Imagen insertada para: " . $prop['titulo'] . "\n";
    } else {
        echo "Archivo no encontrado: " . $prop['imagen'] . "\n";
    }
}

echo "Proceso completado.\n";
?>
