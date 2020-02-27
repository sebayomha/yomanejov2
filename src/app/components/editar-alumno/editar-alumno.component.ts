import { Component, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../services/sharedService/shared-service';
import { Router } from '@angular/router';
import { slideInAnimation } from '../../animations';

@Component({
  selector: 'app-editar-alumno',
  templateUrl: './editar-alumno.component.html',
  styleUrls: ['./editar-alumno.component.css'],
  animations: [
    slideInAnimation
    // animation triggers go here
  ]
})
export class EditarAlumnoComponent implements OnInit {

  constructor(private sharedService:SharedService, private router: Router) { }
  addressesAlumno = [];

  alumnoInformation;

  @ViewChild('direccionFisica') direccionFisica;

  ngOnInit() {
    this.alumnoInformation = this.sharedService.getData();
    console.log(this.sharedService.getData())

    let direccionObject = {
      'idDireccion': this.alumnoInformation.id_DirPrincipal,
      'direccionFormated': this.alumnoInformation.dirPrincipalFormateada
    }

    this.addressesAlumno.push(direccionObject);

    let direccionObjectCopy = Object.assign({}, direccionObject);

    if (this.alumnoInformation.id_DirAlternativa != null) {
      direccionObjectCopy.idDireccion = this.alumnoInformation.id_DirAlternativa;
      direccionObjectCopy.direccionFormated = this.alumnoInformation.dirAlternativaFormateada;
  
      this.addressesAlumno.push(direccionObjectCopy);
    }

    this.alumnoInformation.addressesAlumno = this.addressesAlumno;
  }

  ngAfterViewChecked() {
    this.direccionFisica.setDireccionFisicaDefault();
  }

  volverAlumnos() {
    this.router.navigate(['alumnos']);
  }
}
