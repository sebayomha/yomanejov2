import { Component, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../services/sharedService/shared-service';
import { Router } from '@angular/router';
import { slideInAnimation } from '../../animations';
import { Address } from '../../models/address.model';
import { Search } from '../../models/free-class-finder.model';
import { DatesTimes } from '../../models/dates-times';
import { Option } from '../../models/option';

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

  addressesAlumno = [];
  predefinedHours: Array<string> = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
  locations = ["La Plata", "Berisso", "Ensenada"];
  alumnoInformation;

  addresses = Array<Address>();
  addresses_alt = Array<Address>();

  search: Search;
  control_flag_empty:boolean = false;
  flag_address_alt:boolean = false;
  schedule_send_null:boolean = true;

  constructor(private sharedService:SharedService, private router: Router) { }

  @ViewChild('direccionFisica') direccionFisica;

  ngOnInit() {
    this.alumnoInformation = this.sharedService.getData(); //obtengo la informacion del servicio compartido
    console.log(this.sharedService.getData())

    /* genero el listado de direcciones posibles */
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

    /* fin de genero el listado de direcciones posibles */

    this.setInformationOnInit(); //método encargado de setear todas las disponibilidades y direcciones para el cronograma
  }

  ngAfterViewChecked() {
    this.direccionFisica.setDireccionFisicaDefault();
  }

  volverAlumnos() {
    this.router.navigate(['alumnos']);
  }

  showNameDay(i: number) {
    switch (i) {
      case 0:
        return 'Lunes';
      case 1:
        return 'Martes';
      case 2:
        return 'Miércoles';
      case 3:
        return 'Jueves';
      case 4:
        return 'Viernes';
      case 5:
        return 'Sábado';
      case 6:
        return 'Domingo';
    }
  }

  doScheduleTo(day,hour,index) {
    this.search.dates_times.forEach(element => {
      if (element.name_day == day) {
        element.option[index].hour_start = hour;
        element.option[index].scheduleFrom.forEach( (h:string) => {
          if (h > hour) {
            element.option[index].scheduleTo.push(h);
          }
        })
      }
    })
  }

  setHourFinish(option,hour) {
    option.hour_finish = hour;
    this.control_flag_empty = false;
    option.scheduleSend = [];

    //Armo el array final a enviar.
    option.scheduleFrom.forEach( (h:string) => {
      if (h >= option.hour_start && h <= option.hour_finish) {
        option.scheduleSend.push(h);
      }
    })

    this.schedule_send_null = false;

  }

  setInformationOnInit() {
    this.addresses = [];
    this.addresses_alt = [];
    let dates_times = new Array<DatesTimes>();
/*     for (let i = 0; i <= Object.values(this.alumnoInformation.disponibilidades).length; i++) {
      
      let option = new Option(this.alumnoInformation.disponibilidades[i].tramosHorarios[0], this.alumnoInformation.disponibilidades[i].tramosHorarios[this.alumnoInformation.disponibilidades[i].tramosHorarios.length], this.predefinedHours, this.alumnoInformation.disponibilidades[i].tramosHorarios.slice(1), this.alumnoInformation.disponibilidades[i].tramosHorarios, this.alumnoInformation.disponibilidades[i].tramosHorarios);
      let options = new Array(option);
      let dateTime = new DatesTimes(this.showNameDay(i), false, options);
      dates_times.push(dateTime);
    } */

    Object.values(this.alumnoInformation.disponibilidades).forEach( (disponibilidad:any, index) => {
      if (disponibilidad.todoElDia) { //si es todo el dia
        let option = new Option('', '', this.predefinedHours, [], null, false);
        let options = new Array(option);
        let dateTime = new DatesTimes(this.showNameDay(index), true, options);
        dates_times.push(dateTime);
      } else { //es por tramos o el dia no puede
        if (!disponibilidad.todoElDia && disponibilidad.tramosHorarios.length) { //es por tramos
          let options = new Array();
          
          disponibilidad.tramosHorarios.forEach( (tramo:any) => {
            let option = new Option(
              tramo.horarios[0], 
              tramo.horarios[tramo.horarios.length - 1],
              this.predefinedHours, 
              tramo.horarios.slice(1), 
              tramo.horarios, 
              tramo.usandoDirAlternativa
            );
            options.push(option);
          });
          let dateTime = new DatesTimes(this.showNameDay(index), false, options)
          dates_times.push(dateTime);
        } else { //este dia no puede
          let option = new Option('', '', this.predefinedHours, [], null, false);
          let options = new Array(option);
          let dateTime = new DatesTimes(this.showNameDay(index), false, options);
          dates_times.push(dateTime);
        }
      }    
    });

    //direccion principal del cronograma
    let street = new Address(this.alumnoInformation.calle_DirPrincipal, (this.alumnoInformation.calle_diag_DirPrincipal == 'true'));
    let street_a = new Address('', (this.alumnoInformation.calle_a_diag_DirPrincipal == 'true'), this.alumnoInformation.calle_a_DirPrincipal);
    let street_b = new Address(undefined,(this.alumnoInformation.calle_b_diag_DirPrincipal == 'true'), '', this.alumnoInformation.calle_b_DirPrincipal);
    let altitud = new Address(undefined,false, undefined, undefined, this.alumnoInformation.numero_DirPrincipal);
    let city = new Address(undefined,false, undefined, undefined, undefined, this.alumnoInformation.ciudad_DirPrincipal);
    let floor =  new Address(undefined,false, undefined, undefined, undefined, '', this.alumnoInformation.floor_DirPrincipal);
    let department = new Address(undefined,false, undefined, undefined, undefined, '', '', this.alumnoInformation.departamento_DirPrincipal);
    let obser = new Address(undefined,false, undefined, undefined, undefined, '', '', '', this.alumnoInformation.observaciones_DirPrincipal);
    this.addresses.push(street, street_a, street_b, altitud, city, floor, department, obser);

    //direccion alternativa del cronograma
    if (this.alumnoInformation.id_DirAlternativa != null) {
      this.flag_address_alt = true;
      //direccion principal del cronograma
      let street_alt = new Address(this.alumnoInformation.calle_DirAlternativa, (this.alumnoInformation.calle_diag_DirAlternativa == 'true'));
      let street_a_alt = new Address('', (this.alumnoInformation.calle_a_diag_DirAlternativa == 'true'), this.alumnoInformation.calle_a_DirAlternativa);
      let street_b_alt = new Address(undefined,(this.alumnoInformation.calle_b_diag_DirAlternativa == 'true'), '', this.alumnoInformation.calle_b_DirAlternativa);
      let altitud_alt = new Address(undefined,false, undefined, undefined, this.alumnoInformation.numero_DirAlternativa);
      let city_alt = new Address(undefined,false, undefined, undefined, undefined, this.alumnoInformation.ciudad_DirAlternativa);
      let floor_alt =  new Address(undefined,false, undefined, undefined, undefined, '', this.alumnoInformation.floor_DirAlternativa);
      let department_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', this.alumnoInformation.departamento_DirAlternativa);
      let obser_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '', this.alumnoInformation.observaciones_DirAlternativa);
      this.addresses_alt.push(street_alt, street_a_alt, street_b_alt, altitud_alt, city_alt, floor_alt, department_alt, obser_alt);
    }

    this.search = new Search(this.alumnoInformation.nombre, dates_times, this.addresses, this.addresses_alt, 8, new Date());

    if (this.alumnoInformation.id_DirAlternativa != null) {
      this.search.address_alternative[4].city = "La Plata";
    }
    console.log(this.search);

  }
}
