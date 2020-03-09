import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AlumnosService {

  constructor(private http: HttpClient) { }

  alumno;
  obtenerAlumnos() {
    return this.http.get('api/alumnos');
  }

  updateAlumno(alumno) {
    return this.http.post('api/alumnos/update', alumno);
  }

  setAlumno(alumno) {
    this.alumno = alumno;
  }

  getAlumno() {
    return this.alumno;
  }
}
