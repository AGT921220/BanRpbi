export function buildManifest(doc, manifest, img) {
  const client = manifest.client
  const driver = manifest.driver
  const transportista = manifest.transportista
//   var driver = manifest.driver ?? { 'transportista': { 'razon_social': '', 'address': '', 'autorizacion_spa': '' }, 'vehicle': { 'placas_mx': '', 'model': '' }, 'phone': '' }
//   var transportista = driver.transportista ?? null
//   if (!transportista.cp) {
//   }
  const copia = ['GENERADOR', 'TRANSPORTISTA', 'RECEPTOR', 'GENERADOR']

  for (let i = 0; i < 4; i++) {
    if (i > 0) {
      doc.addPage();
    }
    var canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    var imgData = canvas.toDataURL('image/jpeg');
    doc.setFont("bold")
    doc.setFontSize(16);
    doc.addImage(imgData, 'JPEG', 10, 10, 28, 20);


    doc.setFont("bold")
    doc.setFontSize(10);
    doc.text(70, 15, 'SECRETARÍA DE MEDIO AMBIENTE Y RECURSOS NATURALES');
    doc.setFontSize(9);
    doc.text(75, 20, 'MANIFIESTO DE ENTREGA, TRANSPORTE Y RECEPCIÓN DE');

    const manejoName = 'RESIDUOS PELIGROSOS(GENERADOR)'
    doc.text(80, 24, manejoName);


    //GENERADOR BARRA
    doc.setFillColor(200, 200, 200);
    doc.rect(10, 37, 9, 113, "F");
    doc.rect(10, 37, 9, 113, 'S');
    //TRANSPORTE BARRA
    doc.rect(10, 150, 9, 50, 'S');

    //DESTINATARIO BARRA
    doc.setFillColor(200, 200, 200);
    doc.rect(10, 200, 9, 60, "F");
    doc.rect(10, 200, 9, 60, 'S');


    doc.rect(19, 37, 180, 4, 'S');

    doc.setFont("normal")
    doc.setFontSize(7);
    doc.text(22, 40, '1-NÚM. DE REGISTRO AMBIENTAL:');
    doc.setFont("bold")
    doc.text(65, 40, '' + client.nra);
    doc.setFont("normal")

    doc.rect(98, 37, 0, 4, 'S');
    doc.text(100, 40, '2- NÚM. MANIFIESTO:');
    doc.setFont("bold")
    doc.text(130, 40, '' + manifest.folio);

    doc.setFont("normal")
    doc.rect(168, 37, 0, 4, 'S');
    doc.text(170, 40, '3-PÁGINA');
    doc.setFont("bold")
    let page = i + 1
    doc.text(185, 40, page + '/4');
    
    doc.setFont("normal")
    doc.text(22, 44, '4- NOMBRE O RAZÓN SOCIAL DEL GENERADOR:');
    doc.setFont("bold")
    if (client.name.length > 80) {
      doc.setFontSize(6.5);
    }
    doc.text(82, 44, client.name);
    doc.setFontSize(7);

    doc.setFont("normal")

    doc.setFont("bold")
    doc.setFontSize(12);

    doc.text(16, 110, 'GENERADOR', null, 90);
    doc.text(16, 190, 'TRANSPORTE', null, 90);
    doc.text(16, 244, 'DESTINATARIO', null, 90);


    doc.setFont("normal")
    doc.setFontSize(7);
    doc.rect(19, 45, 180, 0, 'S');
    doc.rect(199, 41, 0, 219, 'S');


    doc.rect(19, 49, 180, 0, 'S');
    doc.text(22, 48, 'DOMICILIO CP:');

    doc.setFont("normal")
    doc.rect(50, 45, 0, 4, 'S');
    doc.rect(138, 45, 0, 4, 'S');
    doc.rect(168, 45, 0, 4, 'S');
    doc.text(51, 48, 'CALLE:');
    doc.text(139, 48, 'NÚM.EXT.');
    doc.text(170, 48, 'NÚM. INT');



    doc.setFont("bold")
    doc.text(40, 48, client.postal_code);
    doc.text(60, 48, client.street);

    doc.setFontSize(6);
    doc.text(152, 48, client.num_ext);
    doc.setFontSize(7);

    doc.text(185, 48, client.num_int ? client.num_int : '');




    doc.setFont("normal")
    doc.rect(88, 49, 0, 4, 'S');
    doc.rect(163, 49, 0, 4, 'S');

    doc.text(22, 52, 'COLONIA:');
    doc.text(90, 52, 'MUNICIPIO O DELEGACION:');
    doc.text(164, 52, 'ESTADO:');

    doc.setFont("bold")

    if (client.colony.length > 35) {
      doc.setFontSize(6);
    }
    if (client.colony.length > 42) {
      doc.setFontSize(5.5);
    }
    doc.text(35, 52, client.colony);
    doc.setFontSize(7);

    doc.text(125, 52, client.city);
    doc.text(175, 52, client.city.state);
    doc.rect(19, 53, 180, 0, 'S');




    doc.setFont("normal")
    doc.rect(92, 53, 0, 4, 'S');

    doc.text(22, 56, 'TELÉFONO:');
    doc.text(95, 56, 'CORREO ELECTRÓNICO: ' + client.contact_email);

    doc.setFont("bold")
    doc.text(38, 56, client.phone ?? '');
    doc.text(128, 56, client.email ? client.email : '');
    doc.rect(19, 57, 180, 0, 'S');


    doc.setFillColor(200, 200, 200);
    doc.rect(19, 57, 179.9, 3.9, "F");
    doc.rect(19, 57, 180, 4, 'S');

    doc.setFont("normal")
    doc.text(80, 60, '5- IDENTIFICACIÓN DE LOS RESIDUOS');

    doc.rect(19, 71, 180, 0, 'S');
    doc.text(40, 67, 'NOMBRE DEL RESIDUO');
    doc.rect(99, 66, 68, 0, 'S');
    doc.rect(99, 61, 0, 10, 'S');


    doc.text(105, 65, 'CLASIFICACIÓN');

    doc.rect(130, 61, 0, 10, 'S');
    doc.text(145, 65, 'ENVASE');


    //ENVASE
    doc.setFontSize(4);
    doc.text(131, 69, 'CANTIDAD');
    doc.text(146, 69, 'TIPO');
    doc.text(156, 69, 'CAPACIDAD');
    doc.rect(140, 66, 0, 5, 'S');
    doc.rect(154, 66, 0, 5, 'S');

    doc.setFontSize(7);


    doc.rect(167, 61, 0, 10, 'S');
    doc.text(169, 65, 'CANTIDAD');
    doc.text(173, 68, 'KG');

    doc.rect(184, 61, 0, 10, 'S');
    doc.text(185.5, 65, 'ETIQUETA');
    doc.rect(184, 66, 15, 0, 'S');
    doc.text(186, 69.5, 'SI');
    doc.text(193, 69.5, 'NO');
    doc.rect(191, 66, 0, 5, 'S');





    doc.text(101, 70, 'C');
    doc.text(106, 70, 'R');
    doc.text(111, 70, 'E');
    doc.text(116, 70, 'T');
    doc.text(121, 70, 'I');
    doc.text(126, 70, 'B');
    // doc.text(131, 70, 'M');

    //CRETIBM LINEAS ABAJO
    doc.rect(104, 66, 0, 5, 'S');
    doc.rect(109, 66, 0, 5, 'S');
    doc.rect(114, 66, 0, 5, 'S');
    doc.rect(119, 66, 0, 5, 'S');
    doc.rect(124, 66, 0, 5, 'S');
    // doc.rect(129, 66, 0, 5, 'S');

    return doc


    var initialPosition = 75

    //   let text = 'UN 3077 SUSTANCIA SOLIDA RESIDUAL POTENCIALMENTE PELIGROSA PARA EL MEDIO AMBIENTE N.E.P. CLASE 9, GRE 171, PG III, (T)'
    var letter = 'A'
    $.each(manifest.details, function (index, detail) {
      if (detail.client_profile.manejo_id == 1) {
        let quantity = detail.service_detail.quantity_new ? detail.service_detail.quantity_new : detail.service_detail.quantity
        createManifestDetailPdf(doc, initialPosition, detail.client_profile, letter, false, manifest.manejo_id, quantity, manifest.destination.clave, detail.service_detail)
        if (letter == 'C') {
          letter = 'D'
        }
        if (letter == 'B') {
          letter = 'C'
        }
        if (letter == 'A') {
          letter = 'B'
        }

        initialPosition = initialPosition + 14
      }

    });
    if (manifest.details.length < 4) {
      let pending = 4 - manifest.details.length
      for (let i = 0; i < pending; i++) {
        createManifestDetailPdfEmpty(doc, initialPosition, letter)
        initialPosition = initialPosition + 14

        if (letter == 'C') {
          letter = 'D'
        }
        if (letter == 'B') {
          letter = 'C'
        }
        if (letter == 'A') {
          letter = 'B'
        }

      }

    }

    doc.setFontSize(6);
    doc.text(20, 130, '6-INSTRUCCIONES ESPECIALES E INFORMACIÓN ADICIONAL PARA EL MANEJO SEGURO: usar equipo de protección personal');
    doc.text(20, 133, '7- Declaro bajo protesta de decir verdad que el contenido de este lote está total y correctamente descrito mediante el número de');
    doc.text(20, 136, 'manifiesto, nombre del residuo, características cretib, debidamente envasado y etiquetado y que se han previsto las condiciones de');
    doc.text(20, 139, 'seguridad para su transporte por vía terrestre de acuerdo con la legislación vigente.');

    doc.setFontSize(7);

    doc.text(150, 135, 'SELLO:');


    doc.text(20, 145, 'NOMBRE Y FIRMA DEL RESPONSABLE');

    doc.setFont("bold")
    doc.text(70, 145, '');
    doc.setFont("normal")

    doc.text(160, 145, 'Fecha:');
    doc.text(170, 148, 'Dia');
    doc.text(176, 148, 'Mes');
    doc.text(182, 148, 'Año');
    doc.rect(19, 150, 180, 0, 'S');


    doc.setFontSize(6);

    doc.text(20, 154, '8- NOMBRE O RAZÓN SOCIAL DEL TRANSPORTISTA:');
    doc.setFont("bold")
    doc.text(75, 154, transportista.razon_social ?? '');
    doc.setFont("normal")

    doc.rect(19, 156, 180, 0, 'S');
    doc.text(20, 159, 'DOMICILIO:');
    doc.setFont("bold")
    doc.text(32, 159, 'CP:');
    doc.setFont("normal")


    doc.text(38, 159, transportista.cp ?? '');
    doc.rect(50, 156, 0, 5, 'S');

    doc.setFont("bold")
    doc.text(52, 159, 'CALLE:');
    doc.setFont("normal")
    doc.text(65, 159, transportista.street ?? '');

    doc.rect(146, 156, 0, 5, 'S');
    doc.setFont("bold")
    doc.text(148, 159, 'NUM EXT:');
    doc.setFont("normal")
    doc.text(160, 159, '' + transportista.num_ext ?? 'N/A');
    doc.rect(175, 156, 0, 5, 'S');
    doc.setFont("bold")
    doc.text(177, 159, 'NUM INT:');
    doc.setFont("normal")
    doc.text(190, 159, '' + transportista.num_int ?? 'N/A');



    doc.rect(19, 161, 180, 0, 'S');
    doc.setFont("bold")
    doc.text(20, 164, 'COLONIA:');
    doc.setFont("normal")
    doc.text(32, 164, '' + transportista.colonia ?? '');
    doc.rect(78, 161, 0, 5, 'S');
    doc.setFont("bold")
    doc.text(80, 164, 'MUNICIPIO O DELEGACIÓN:');
    doc.setFont("normal")
    doc.text(112, 164, '' + transportista.city ?? '');
    doc.rect(148, 161, 0, 5, 'S');
    doc.setFont("bold")
    doc.text(150, 164, 'ESTADO:');
    doc.setFont("normal")
    doc.text(165, 164, '' + transportista.state ?? '');

    doc.rect(19, 166, 180, 0, 'S');
    doc.text(20, 169, 'TELEFONO:');
    doc.text(35, 169, '' + (transportista.phone || ''));


    doc.rect(19, 171, 180, 0, 'S');
    doc.text(20, 174, '9- NÚM. DE AUTORIZACIÓN DE LA SEMARNAT:');
    doc.text(70, 174, '' + manifest.aut_semarnat_transport ?? '');


    doc.rect(118, 171, 0, 5, 'S');
    doc.text(120, 174, '10- NÚM. DE PERMISO S.C.T.:');
    var vehicle = manifest.vehicle
    doc.text(155, 174, `${vehicle.sct || ''}`);

    doc.rect(118, 176, 0, 5, 'S');
    doc.text(120, 179, '12- NÚM. DE PLACA:');
    doc.text(145, 179, ''+manifest.transport_placas);




    doc.text(150, 190, 'SELLO:');


    doc.text(160, 196, 'Fecha:');
    doc.text(170, 198, 'Dia');
    doc.text(176, 198, 'Mes');
    doc.text(182, 198, 'Año');



    doc.rect(19, 176, 180, 0, 'S');
    doc.setFont("bold")
    doc.text(20, 179, '11-TIPO DE VEHÍCULO:');
    doc.setFont("normal")
    doc.text(45, 179, '' + vehicle.type ?? 'N/A');


    doc.setFont("bold")
    doc.rect(19, 181, 180, 0, 'S');
    doc.text(20, 184, '13- RUTA DE LA EMPRESA GENERADORA HASTA SU ENTREGA:');
    doc.setFont("normal")
    doc.text(90, 184, manifest.route ?? 'N/A');


    doc.rect(19, 186, 180, 0, 'S');
    doc.text(20, 189, '14- Declaro bajo protesta de decir verdad que recibí los residuos peligrosos descritos en el manifiesto para su transporte a la empresa');
    doc.text(20, 191, 'destinataria señalada por el generador.');
    doc.text(40, 196, 'NOMBRE Y FIRMA DEL RESPONSABLE');

    doc.rect(19, 200, 180, 0, 'S');

    let destination = manifest.destination ?? {
      'name': '',
      'cp': '',
      'calle': '',
      'num_ext': '',
      'num_int': '',
      'colonia': '',
      'municipio': '',
      'estado': '',
      'phone': '',
      'email': ''
    };

    doc.text(20, 203, '15- NOMBRE O RAZÓN SOCIAL DEL DESTINATARIO:');


    doc.text(75, 203, destination.name);

    doc.rect(19, 205, 180, 0, 'S');
    doc.text(20, 208, 'DOMICILIO:');


    doc.setFont("bold")
    doc.text(32, 208, 'CP:');
    doc.text(38, 208, destination.cp ?? '');
    doc.setFont("normal")
    doc.rect(50, 205, 0, 5, 'S');

    doc.setFont("bold")
    doc.text(52, 208, 'CALLE:');
    doc.text(65, 208, destination.calle ?? '');
    doc.setFont("normal")
    doc.rect(146, 205, 0, 5, 'S');



    doc.setFont("bold")
    doc.text(148, 208, 'NUM EXT:');
    doc.text(160, 208, destination.num_ext ?? '');
    doc.setFont("normal")
    doc.rect(175, 205, 0, 5, 'S');


    doc.setFont("bold")
    doc.text(177, 208, 'NUM INT:');
    doc.text(190, 208, destination.num_int ?? '');
    doc.setFont("normal")




    doc.rect(19, 210, 180, 0, 'S');
    doc.text(20, 213, 'COLONIA:');

    doc.setFont("bold")
    doc.text(32, 213, destination.colonia ?? '');
    doc.setFont("normal")



    doc.rect(78, 210, 0, 5, 'S');
    doc.text(80, 213, 'MUNICIPIO O DELEGACIÓN:');
    doc.setFont("bold")
    doc.text(112, 213, destination.municipio ?? '');
    doc.setFont("normal")

    doc.rect(148, 210, 0, 5, 'S');
    doc.text(150, 213, 'ESTADO:');
    doc.setFont("bold")
    doc.text(165, 213, destination.estado ?? '');
    doc.setFont("normal")


    doc.rect(19, 215, 180, 0, 'S');
    doc.text(20, 218, 'TELEFONO:');
    doc.setFont("bold")
    doc.text(34, 218, '' + (destination.phone || ''));
    doc.setFont("normal")


    doc.rect(70, 215, 0, 5, 'S');
    doc.text(72, 218, 'CORREO ELECTRÓNICO:');
    doc.setFont("bold")
    doc.text(100, 218, '' + (destination.email || ''));
    doc.setFont("normal")





    doc.rect(19, 220, 180, 0, 'S');
    doc.text(20, 223, '16- NÚM. DE AUTORIZACIÓN DE LA SEMARNAT:');

    doc.setFont("bold")
    doc.text(72, 223, '' + (manifest.aut_semarnat || ''));
    doc.setFont("normal")



    doc.rect(19, 225, 180, 0, 'S');
    doc.text(20, 228, '17- NOMBRE Y CARGO DE LA PERSONA QUE RECIBE LOS RESIDUOS:');

    doc.setFont("bold")
    doc.text(94, 228, '');
    doc.setFont("normal")



    doc.rect(19, 230, 180, 0, 'S');
    doc.text(20, 233, '18 OBSERVACIONES');


    doc.rect(19, 244, 180, 0, 'S');
    doc.text(20, 248, '19- Declaro bajo protesta de decir verdad que recibí los residuos peligrosos descritos en el manifiesto.');

    doc.text(20, 254, 'NOMBRE Y FIRMA DEL RESPONSABLE');
    doc.setFont("bold")
    doc.text(24, 258, '' + manifest.destination.charge_and_name ?? ' ');
    doc.setFont("normal")


    doc.text(150, 252, 'SELLO:');


    doc.text(160, 256, 'Fecha:');
    doc.text(170, 258, 'Dia');
    doc.text(176, 258, 'Mes');
    doc.text(182, 258, 'Año');

    doc.rect(19, 260, 180, 0, 'S');


    doc.setFontSize(10);
    doc.setFont('bold')

    // doc.text(10, 265, copia[i]);
    i === 0 ? doc.text(10, 265, 'ORIGINAL:') : doc.text(10, 265, 'COPIA:')

    doc.setFont('normal')

    i === 0 ? doc.text(30, 265, copia[i]) : doc.text(25, 265, copia[i])


  }
  return doc

}