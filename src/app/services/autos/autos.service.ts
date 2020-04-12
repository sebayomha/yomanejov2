import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AutosService {

  constructor(private http: HttpClient) { }

  obtenerAutos() {
    return this.http.get('api/autos');
  }

  obtenerZonas() {
    return this.http.get('api/autos/zonas');
  }

  crearAuto(data){
    return this.http.post('api/autos/crear', data); 
  }

  modificarAuto(data){
    return this.http.post('api/autos/modificar', data); 
  }

  bajarAuto(data) {
    return this.http.post('api/autos/bajar', data); 
  }

}
