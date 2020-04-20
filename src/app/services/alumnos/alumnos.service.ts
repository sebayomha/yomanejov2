import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { BASE_URL } from '../../configVariables';

@Injectable({
  providedIn: 'root'
})
export class AlumnosService {

  constructor(private http: HttpClient) { }

  obtenerAlumnos() {
    return this.http.get(`${BASE_URL}/api/alumnos`);
  }

  updateAlumno(alumno) {
    return this.http.post(`${BASE_URL}/api/alumnos/update`, alumno);
  }

  getInformacionPersonal(idAlumno) {
    const params = new HttpParams().set('idAlumno', idAlumno);
    return this.http.get(`${BASE_URL}/api/alumnos/getInformacionPersonal`, {params: params});
  }

  eliminarAlumno(data) {
    return this.http.post(`${BASE_URL}/api/alumnos/eliminar`, data); 
  }
}
