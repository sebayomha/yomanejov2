import { Component, OnInit, ViewChild } from '@angular/core';
import { AlumnosService } from '../../services/alumnos/alumnos.service';
import { Response } from '../../models/response';
import { MatTableDataSource} from '@angular/material/table';
import { Router } from '@angular/router';
import { SharedService } from '../../services/sharedService/shared-service';

@Component({
  selector: 'app-students',
  templateUrl: './students.component.html',
  styleUrls: ['./students.component.css']
})
export class StudentsComponent implements OnInit {

  alumnosActivos;
  alumnosInactivos;
  displayedColumns: string[] = ['No', 'nombre', 'direccion', 'telefono', 'documento', 'accion'];
  busquedaAlumnoActivo: string = '';
  busquedaAlumnoInctivos: string = '';

  
  detailedAlumno;
  @ViewChild('studentDetail') studentDetail;

  constructor(private alumnoService: AlumnosService, private router: Router, private sharedService:SharedService) { }

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

  editarAlumno(alumno, $event) {
    this.sharedService.setData(alumno)
    this.router.navigate(['alumnos/editar/', alumno.idAlumno]);
    $event.stopPropagation()
    console.log("EDITAR")
  }

  eliminarAlumno(element, $event) {
    $event.stopPropagation()
    console.log("ELIMINAR")
  }

}
