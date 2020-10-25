<?php

// ================================================
// SPAW PHP WYSIWYG editor control
// ================================================
// Spanish language file
// ================================================
// Developed: Alan Mendelevich, alan@solmetra.lt
// Copyright: Solmetra (c)2005 All rights reserved.
// Spanish translation: Xavi Gil (dgil@tinet.org)
// ------------------------------------------------
//                                www.solmetra.com
// ================================================
// v.1.0, 2005-03-20
// ================================================

// charset to be used in dialogs
$spaw_lang_charset = 'iso-8859-1';

// language text data array
// first dimension - block, second - exact phrase
// alternative text for toolbar buttons and title for dropdowns - 'title'

$spaw_lang_data = [
    'cut' => [
        'title' => 'Cortar',
    ],
'copy' => [
        'title' => 'Copiar',
    ],
'paste' => [
        'title' => 'Pegar',
    ],
'undo' => [
        'title' => 'Deshacer',
    ],
'redo' => [
        'title' => 'Rehacer',
    ],
'hyperlink' => [
        'title' => 'Enlace',
    ],
'image_insert' => [
        'title' => 'Insertar imagen',
'select' => 'Seleccionar',
'cancel' => 'Cancelar',
'library' => 'Libreria',
'preview' => 'Previsualizar',
'images' => 'Imagenes',
'upload' => 'Subir imagen',
'upload_button' => 'Subir',
'error' => 'Error',
'error_no_image' => 'Por favor, selecciona una imaágen',
'error_uploading' => 'Ocurrió un error al subir la imágene, intentelo de nuevo',
'error_wrong_type' => 'Tipo de imágen incorrecto.',
'error_no_dir' => 'La libreria no existe',
    ],
'image_prop' => [
        'title' => 'Propiedades de la imágen',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
'source' => 'Source',
'alt' => 'Texto alternativo',
'align' => 'Alineación',
'left' => 'izquierda',
'right' => 'derecha',
'top' => 'superior',
'middle' => 'medio',
'bottom' => 'inferior',
'absmiddle' => 'medio absoluto',
'texttop' => 'Texto superior',
'baseline' => 'baselina',
'width' => 'Anchura',
'height' => 'Altura',
'border' => 'Borde',
'hspace' => 'Espacio hor.',
'vspace' => 'Espaco vert.',
'error' => 'Error',
'error_width_nan' => 'la altura debe ser un número',
'error_height_nan' => 'la anchura debe ser un número',
'error_border_nan' => 'el borde debe ser un número',
'error_hspace_nan' => 'el espaciado horizontal debe ser un número',
'error_vspace_nan' => 'la espaciado vertical debe ser un número',
    ],
'hr' => [
        'title' => 'Línea horizontal',
    ],
'table_create' => [
        'title' => 'Crear tabla',
    ],
'table_prop' => [
        'title' => 'Propiedades de la tabla',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
'rows' => 'Filas',
'columns' => 'Columnas',
'width' => 'Ancho',
'height' => 'Alto',
'border' => 'Borde',
'pixels' => 'pixels',
'cellpadding' => 'Relleno celda',
'cellspacing' => 'Espaciado celda',
'bg_color' => 'Color de fondo',
'error' => 'Error',
'error_rows_nan' => 'Filas debe ser un número',
'error_columns_nan' => 'Columnas debe ser un número',
'error_width_nan' => 'Ancho debe ser un número',
'error_height_nan' => 'Alto debe ser un número',
'error_border_nan' => 'Borde debe ser un número',
'error_cellpadding_nan' => 'Relleno debe ser un número',
'error_cellspacing_nan' => 'Espaciado debe ser un número',
    ],
'table_cell_prop' => [
        'title' => 'Propiedades de la celda',
'horizontal_align' => 'Alineación horizontal',
'vertical_align' => 'Alineación vertical',
'width' => 'Ancho',
'height' => 'Alto',
'css_class' => 'Estilo CSS',
'no_wrap' => 'No wrap',
'bg_color' => 'Color de fondo',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
'left' => 'Izquierda',
'center' => 'Centro',
'right' => 'Derecha',
'top' => 'Arriba',
'middle' => 'Medio',
'bottom' => 'Abajo',
'baseline' => 'Baselina',
'error' => 'Error',
'error_width_nan' => 'Ancho debe ser un número',
'error_height_nan' => 'Alto debe ser un número',
    ],
'table_row_insert' => [
        'title' => 'Insertar fila',
    ],
'table_column_insert' => [
        'title' => 'Insertar columna',
    ],
'table_row_delete' => [
        'title' => 'Borrar fila',
    ],
'table_column_delete' => [
        'title' => 'Borrar columna',
    ],
'table_cell_merge_right' => [
        'title' => 'Combinar celdas izquierda',
    ],
'table_cell_merge_down' => [
        'title' => 'Combinar celdas abajo',
    ],
'table_cell_split_horizontal' => [
        'title' => 'Divicir celdas horizontalmente',
    ],
'table_cell_split_vertical' => [
        'title' => 'Dividir celadas verticalmente',
    ],
'style' => [
        'title' => 'Estilo',
    ],
'font' => [
        'title' => 'Fuente',
    ],
'fontsize' => [
        'title' => 'Tamaño',
    ],
'paragraph' => [
        'title' => 'Párrafo',
    ],
'bold' => [
        'title' => 'Negrita',
    ],
'italic' => [
        'title' => 'Cursiva',
    ],
'underline' => [
        'title' => 'Subrayado',
    ],
'ordered_list' => [
        'title' => 'Lista ordenada',
    ],
'bulleted_list' => [
        'title' => 'Lista con marca',
    ],
'indent' => [
        'title' => 'Sangria',
    ],
'unindent' => [
        'title' => 'Anular sangria',
    ],
'left' => [
        'title' => 'Izquierda',
    ],
'center' => [
        'title' => 'Centro',
    ],
'right' => [
        'title' => 'Derecha',
    ],
'fore_color' => [
        'title' => 'Color fuente',
    ],
'bg_color' => [
        'title' => 'Color de fondo',
    ],
'design_tab' => [
        'title' => 'Cambiar a modo WYSIWYG (diseño)',
    ],
'html_tab' => [
        'title' => 'Cambiar a modo HTML (código)',
    ],
'colorpicker' => [
        'title' => 'Selecciona color',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
    ],
    // <<<<<<<<< NEW >>>>>>>>>

'cleanup' => [
        'title' => 'Limipiador de HTML (borra los estilos)',
'confirm' => 'Con esta acción borraras todos los estilos, fuentes y tags menos usados. Algunas cosas de tu formato pueden desaparecer.',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
    ],
'toggle_borders' => [
        'title' => 'Bordes Toggle',
    ],
'hyperlink' => [
        'title' => 'Enlace',
'url' => 'URL',
'name' => 'Nombre',
'target' => 'Destino',
'title_attr' => 'Titulo',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
    ],
'table_row_prop' => [
        'title' => 'Propiedades de la fila',
'horizontal_align' => 'Alineación horizontal',
'vertical_align' => 'Alineación vertical',
'css_class' => 'Clase CSS',
'no_wrap' => 'Sin separación',
'bg_color' => 'Color de fondo',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
'left' => 'Izquierda',
'center' => 'Centro',
'right' => 'Derecha',
'top' => 'Arriba',
'middle' => 'Enmedio',
'bottom' => 'Abajo',
'baseline' => 'Baselina',
    ],
'symbols' => [
        'title' => 'Carácteres especiales',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
    ],
'templates' => [
        'title' => 'Plantillas',
    ],
'page_prop' => [
        'title' => 'Propiedades de la página',
'title_tag' => 'Titulo',
'charset' => 'Juego carácteres',
'background' => 'Imágen de fondo',
'bgcolor' => 'Color de fondo',
'text' => 'Color texto',
'link' => 'Color Enlaces',
'vlink' => 'Color enlace visitado',
'alink' => 'Color enlace activado',
'leftmargin' => 'Margen izquierdo',
'topmargin' => 'Margen superior',
'css_class' => 'Clase CSS',
'ok' => '   OK   ',
'cancel' => 'Cancelar',
    ],
'preview' => [
        'title' => 'Previsualizar',
    ],
'image_popup' => [
        'title' => 'Image en popup',
    ],
'zoom' => [
        'title' => 'Zoom',
    ],
];


