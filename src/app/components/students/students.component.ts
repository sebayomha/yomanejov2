import { Component, OnInit } from '@angular/core';
import { AlumnosService } from '../../services/alumnos/alumnos.service';
import { Response } from '../../models/response';

@Component({
  selector: 'app-students',
  templateUrl: './students.component.html',
  styleUrls: ['./students.component.css']
})
export class StudentsComponent implements OnInit {

  alumnosActivos;
  alumnosInactivos;
  displayedColumns: string[] = ['No', 'nombre', 'direccion', 'telefono'];
  
  constructor(private alumnoService: AlumnosService) { }

  ngOnInit() {
    this.alumnoService.obtenerAlumnos().subscribe( (response: Response) => {
      this.alumnosActivos = this.obtenerAlumnosActivos(response.data);
      this.alumnosInactivos = this.obtenerAlumnosInactivos(response.data);
      console.log(this.alumnosActivos)
      console.log(this.alumnosInactivos)
    })
  }

  obtenerAlumnosActivos(alumnos: Array<any>) {
    return alumnos.filter( alumno => {
      if (alumno.activo == 'true') {
        return true;
      }
      return false;
    })
  }

  obtenerAlumnosInactivos(alumnos: Array<any>) {
    return alumnos.filter( alumno => {
      if (alumno.activo == 'false') {
        return true;
      }
      return false;
    })
  }

}
