import { Component, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../services/sharedService/shared-service';
import { Router } from '@angular/router';
import { slideInAnimation } from '../../animations';
import { Address } from '../../models/address.model';
import { Search } from '../../models/free-class-finder.model';
import { DatesTimes } from '../../models/dates-times';
import { Option } from '../../models/option';
import { BreakpointObserver } from '@angular/cdk/layout';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { MatSnackBar } from '@angular/material/snack-bar';

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
  alumnoInformationCopyPersistData;

  addresses = Array<Address>();
  addresses_alt = Array<Address>();

  search: Search;
  control_flag_empty:boolean = false;
  flag_address_alt:boolean = false;
  schedule_send_null:boolean = true;

  documento;
  durationInSeconds = 3;
  available_schedules;
  numberOfClasses: number;
  excepciones: Array<any> =[];
  
  constructor(private _snackBar: MatSnackBar, private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private sharedService:SharedService, private router: Router) { }

  @ViewChild('direccionFisica') direccionFisica;
  @ViewChild('editingStudentForm') editingStudentForm;

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
    this.numberOfClasses = this.alumnoInformation.cantClasesParaRestantes;
    /* fin de genero el listado de direcciones posibles */

    this.setInformationOnInit(); //método encargado de setear todas las disponibilidades y direcciones para el cronograma
  }

  ngAfterViewInit() {
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

    Object.values(this.alumnoInformation.disponibilidades).forEach( (disponibilidad:any, index) => {
      if (disponibilidad.todoElDia) { //si es todo el dia
        let option = new Option('', '', this.predefinedHours, [], this.predefinedHours, false);
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
    } else { //no posee direccion alternativa asi que seteamos todo vacio
      let street_alt = new Address('',false);
      let street_a_alt = new Address(undefined, false, '');
      let street_b_alt = new Address(undefined,false, undefined, '');
      let altitud_alt = new Address(undefined,false, undefined, undefined, '');
      let city_alt = new Address(undefined,false, undefined, undefined, undefined, 'La Plata');
      let floor_alt =  new Address(undefined,false, undefined, undefined, undefined, '', '');
      let department_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '');
      let obser_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '', '');
      this.addresses_alt.push(street_alt, street_a_alt, street_b_alt, altitud_alt, city_alt, floor_alt, department_alt, obser_alt);
    }

    this.search = new Search(this.alumnoInformation.nombre, dates_times, this.addresses, this.addresses_alt, this.alumnoInformation.cantClasesParaRestantes, new Date(), this.alumnoInformation.telefono);
    
    this.documento = this.alumnoInformation.documento;
    this.alumnoInformationCopyPersistData = JSON.parse(JSON.stringify(this.alumnoInformation));
  }

  //validar los formularios
  continueEditing() {
    console.log(this.modificoDatosPersonales());
    if (this.editingStudentForm.valid && this.direccionFisica.validateForm() && !this.schedule_send_null)
     return false;
    
    let object = JSON.parse(JSON.stringify(this.search));
    
    //Transformo los nombres de los dias en ingles
    for (let i = 0; i <=6; i++) {
      switch (i) {
        case 0:
          object.dates_times[i].name_day = 'Monday';
          break;
        case 1:
          object.dates_times[i].name_day = 'Tuesday';
          break;
        case 2:
          object.dates_times[i].name_day = 'Wednesday';
          break;
        case 3:
          object.dates_times[i].name_day = 'Thursday';
          break;
        case 4:
          object.dates_times[i].name_day = 'Friday';
          break;
        case 5:
          object.dates_times[i].name_day = 'Saturday';
          break;
        case 6:
          object.dates_times[i].name_day = 'Sunday';
          break;
      }
    }

    console.log(this.search);
    this.cronogramaService.getCronograma(object, []).subscribe( (response: Response) => {
      console.log(response)
      switch (response.code) {
        case 0:
          this.available_schedules = Object.values(response.data);
          this._snackBar.dismiss();
          break;
        case 2:
        case 3:{
          this.available_schedules = null;
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response
          });
        }
        break;
      }
    });
  }

  preventLetters($event) {
    var reg = /^[0-9]+$/i;
    if (!reg.test($event.key)) {
      $event.preventDefault();
    }
  }

  modificoDatosPersonales() {
    console.log("this.alumnoInformation: ", this.alumnoInformation)
    console.log("this.alumnoInformationCopyPersistData: ", this.direccionFisica.getData())
    
    let direccionFisicaInformation = this.direccionFisica.getData();

    if ( (this.alumnoInformation.telefono.replace(/\s/g, "").replace('-', "")) == (this.search.student_phone.toString().replace(/\s/g, "").replace('-', "")) 
    && this.alumnoInformation.documento == this.documento 
    && this.alumnoInformation.nombre == this.search.student_name) {

      if (direccionFisicaInformation.nuevaDireccion) { //estamos sobre nueva direccion
        //datos primarios personales continuan iguales, evaluo si la direccion fisica cambio. Solo evaluo los datos sensibles. (dpto y piso y obs no importan)
        if (this.alumnoInformation.calle_DirFisica == direccionFisicaInformation.direccion.street
          && this.alumnoInformation.ciudad_DirFisica == direccionFisicaInformation.direccion.city
          && this.alumnoInformation.numero_DirFisica == direccionFisicaInformation.direccion.altitud
          ) { //los datos personales continuan igual
            return false;
          } else { //modifico datos personales de la direccion fisica
            return true;
          }
      } else { //es una direccion que ya existe entonces tengo que comparar los ids
        if (direccionFisicaInformation.idDireccionSeleccionada == this.alumnoInformation.id_DirFisica) { //es la misma
          return false;
        } else {
          return true;
        }
      }
    } else { //modifico datos personales
      return true;
    }
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  quitDA(){
    //Esta funcion resetea todas las direcciones alternativas.
    if (!this.flag_address_alt) {
      for (let i = 0; i <=6; i++) {
        this.search.dates_times[i].option.forEach(opt => {
          if ( opt.dir_alt == true ) {
            opt.dir_alt = false;
          }
        });
      }
    }
  }

  allDay(day) {

    let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

    if (index != -1) {
      if (this.search.dates_times[index].all_day == false ) {
        this.search.dates_times[index].all_day = true;
        this.schedule_send_null = false;

        if (this.search.dates_times[index].option.length > 1) {
          let length = this.search.dates_times[index].option.length;
          this.search.dates_times[index].option.splice( 1, length );
        }

        this.search.dates_times[index].option[0].hour_start = '';
        this.search.dates_times[index].option[0].hour_finish = '';
        this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
        this.search.dates_times[index].option[0].scheduleTo = [];
        this.search.dates_times[index].option[0].scheduleSend = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
        this.search.dates_times[index].option[0].dir_alt = false;

      } else {
        this.search.dates_times[index].all_day = false;
        this.search.dates_times[index].option[0].scheduleSend = null;
        this.search.dates_times[index].option[0].dir_alt = false;

        this.schedule_send_null = true;
        for (let i = 0; i <=6; i++) {
          if (this.search.dates_times[i].all_day == false) {
            if(this.search.dates_times[i].option[0].scheduleSend != null) {
              this.schedule_send_null = false;
            }
          } else {
            this.schedule_send_null = false;
          }
        }
        this.control_flag_empty = false;
      }
    }
  }

  removeOption(index, day_options) {

    if (day_options.length > 1) {
    day_options.splice( index, 1 );
    } else {
      day_options[index].hour_start = '';
      day_options[index].hour_finish = '';
      day_options[index].scheduleTo = [];
      day_options[index].scheduleSend = null;
    }
    this.control_flag_empty = false;
    this.schedule_send_null = true;

    for (let i = 0; i <=6; i++) {
      if (this.search.dates_times[i].all_day == true) {
        return this.schedule_send_null = false;
      } else {
        if(this.search.dates_times[i].option[0].scheduleSend != null) {
          this.schedule_send_null = false;
        }
      }
    }
  }

  addDateTime(day) {
    let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

    if (index != -1) {
      if(this.search.dates_times[index].option.length == 1 && this.search.dates_times[index].option[0].hour_start == '' && this.search.dates_times[index].option[0].hour_finish == ''){

          this.search.dates_times[index].option[0].hour_start = null;
          this.search.dates_times[index].option[0].hour_finish = null;
          this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
          this.search.dates_times[index].option[0].scheduleTo = [];
          this.search.dates_times[index].option[0].scheduleSend = null;

      } else {
        let option_length = this.search.dates_times[index].option.length;

        let j = option_length - 1;

        let hour_finish_selected = this.search.dates_times[index].option[j].hour_finish;

        let new_schedule_from = [];

        this.search.dates_times[index].option[j].scheduleFrom.forEach( (h:string) => {
          if (h > hour_finish_selected ) {
            new_schedule_from.push(h);
          }
        })

        this.search.dates_times[index].option.push({
          hour_start: null,
          hour_finish: null,
          scheduleFrom: new_schedule_from,
          scheduleTo: [],
          scheduleSend: null,
          dir_alt: false
        });
      }
    }
    this.control_flag_empty = true;
  }

}
