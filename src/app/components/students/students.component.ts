import { Component, OnInit, ViewChild } from '@angular/core';
import { AlumnosService } from '../../services/alumnos/alumnos.service';
import { Response } from '../../models/response';
import { MatTableDataSource} from '@angular/material/table';
import { Router } from '@angular/router';
import { SharedService } from '../../services/sharedService/shared-service';
import { MatSidenav } from '@angular/material/sidenav';

@Component({
  selector: 'app-students',
  templateUrl: './students.component.html',
  styleUrls: ['./students.component.css']
})
export class StudentsComponent implements OnInit {

  alumnosActivos;
  alumnosInactivos;
  alumnosPendientes;
  displayedColumns: string[] = ['No', 'nombre', 'direccion', 'telefono', 'documento', 'accion'];
  busquedaAlumnoActivo: string = '';
  busquedaAlumnoInctivos: string = '';

  alumno;
  dataToEliminarAlumno;
  showSuccessBanner: boolean = false;

  alumnosActivosLength: number;
  alumnosInactivosLength: number;
  alumnosPendientesLength: number;

  @ViewChild('studentDetail') studentDetail;
  @ViewChild('sidenav') sidenav: MatSidenav;
  @ViewChild('customModal') customModal;

  constructor(private alumnoService: AlumnosService, private router: Router, private sharedService:SharedService) { }

  ngOnInit() {
    this.sharedService.destroyData();
    this.alumnoService.obtenerAlumnos().subscribe( (response: Response) => {
      console.log(response.data)
      this.alumnosActivos = new MatTableDataSource(this.obtenerAlumnosActivos(response.data));
      this.alumnosInactivos = new MatTableDataSource(this.obtenerAlumnosInactivos(response.data));
      this.alumnosPendientes = new MatTableDataSource(this.obtenerAlumnosPendientes(response.data));
      this.alumnosInactivosLength = this.alumnosInactivos.data.length;
      this.alumnosActivosLength = this.alumnosActivos.data.length;
      this.alumnosPendientesLength = this.alumnosPendientes.data.length;
      console.log(this.alumnosPendientesLength)
      console.log(this.alumnosActivos)
      console.log(this.alumnosInactivos)
    })
  }

  obtenerAlumnosActivos(alumnos: Array<any>) {
    return alumnos.filter( alumno => {
      if (alumno.activo == 'true' && alumno.confirmado == 'true') {
        return true;
      }
      return false;
    })
  }

  obtenerAlumnosPendientes(alumnos: Array<any>) {
    return alumnos.filter( alumno => {
      if (alumno.confirmado == 'false') {
        return true;
      }
      return false;
    })
  }

  obtenerAlumnosInactivos(alumnos: Array<any>) {
    return alumnos.filter( alumno => {
      if (alumno.activo == 'false' && alumno.confirmado == 'true') {
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
    if (operation == 'AlumnosInactivos')
    this.alumnosInactivos.filter = filterValue.trim().toLowerCase();
    else
    this.alumnosPendientes.filter = filterValue.trim().toLowerCase();
  }

  openDetail(alumno) {
    this.alumno = alumno;
    this.sidenav.open();
  }
  
  closedStudentDetail() {
    this.alumno = null;
    this.sidenav.close();
  }

  editarAlumno(alumno, $event) {
    this.sharedService.setData(alumno)
    this.router.navigate(['alumnos/editar/', alumno.idAlumno]);
    $event.stopPropagation()
  }

  eliminarAlumno(element, $event) {
    $event.stopPropagation()
    this.dataToEliminarAlumno = element;
    this.customModal.open();
    console.log("ELIMINAR")
  }

  confirmEliminarAlumno($event) {
    console.log("confirmo la baja ", $event);
  }

  goToCronograma(idCronograma) {
    this.router.navigate(['pendientes', idCronograma]);
    console.log(idCronograma);
  }

}
