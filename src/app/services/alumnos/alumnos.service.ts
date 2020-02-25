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
}
