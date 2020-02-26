import { Component, OnInit, ViewChild } from '@angular/core';
import { AlumnosService } from '../../services/alumnos/alumnos.service';
import { Response } from '../../models/response';
import {MatTableDataSource} from '@angular/material/table';

@Component({
  selector: 'app-students',
  templateUrl: './students.component.html',
  styleUrls: ['./students.component.css']
})
export class StudentsComponent implements OnInit {

  alumnosActivos;
  alumnosInactivos;
  displayedColumns: string[] = ['No', 'nombre', 'direccion', 'telefono', 'documento'];
  busquedaAlumnoActivo: string = '';
  busquedaAlumnoInctivos: string = '';

  detailedAlumno;
  @ViewChild('studentDetail') studentDetail;

  constructor(private alumnoService: AlumnosService) { }

  ngOnInit() {
    this.alumnoService.obtenerAlumnos().subscribe( (response: Response) => {
      this.alumnosActivos = new MatTableDataSource(this.obtenerAlumnosActivos(response.data));
      this.alumnosInactivos = new MatTableDataSource(this.obtenerAlumnosInactivos(response.data));
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

  applyFilter(event: Event, operation) {
    const filterValue = (event.target as HTMLInputElement).value;
    if (operation == 'AlumnosActivos')
    this.alumnosActivos.filter = filterValue.trim().toLowerCase();
    else
    this.alumnosInactivos.filter = filterValue.trim().toLowerCase();
  }

  openDetail(alumno) {
    this.detailedAlumno = alumno;
    console.log(this.studentDetail)
    this.studentDetail.open();
  }
  
  closedStudentDetail($event) {
    this.detailedAlumno = null;
  }

}
