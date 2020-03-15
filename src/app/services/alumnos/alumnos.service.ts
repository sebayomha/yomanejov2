import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AlumnosService {

  constructor(private http: HttpClient) { }

  obtenerAlumnos() {
    return this.http.get('api/alumnos');
  }

  updateAlumno(alumno) {
    return this.http.post('api/alumnos/update', alumno);
  }

  getInformacionPersonal(idAlumno) {
    const params = new HttpParams().set('idAlumno', idAlumno);
    return this.http.get('api/alumnos/getInformacionPersonal', {params: params});
  }

  eliminarAlumno(data) {
    return this.http.post('api/alumnos/eliminar', data); 
  }
}
