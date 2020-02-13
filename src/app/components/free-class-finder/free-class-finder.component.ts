import { Component } from '@angular/core';
import { Search } from '../../models/free-class-finder.model';
import { DatesTimes } from '../../models/dates-times';
import { Option } from '../../models/option';
import { CronogramaService } from '../../services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';
import { Address } from '../../models/address.model';
import { Excepcion } from 'src/app/models/excepcion';
import { ExcepcionRowTIme } from 'src/app/models/excepcion-row-time';
import { DatePipe } from '@angular/common';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';

@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss', '../../global-styles.scss']
})

export class FreeClassFinderComponent {

  excepciones = Array<Excepcion>();
  addresses = Array<Address>();
  addresses_alt = Array<Address>();
  minDate = new Date();
  locations = ["La Plata", "Berisso", "Ensenada"];
  predefinedHours = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
  search: Search;
  control_flag_empty:boolean = false;
  control_collapse_search:boolean = false;
  schedule_send_null:boolean = true;
  available_schedules:any;
  durationInSeconds = 3;
  student_name = '';
  flag_address_alt:boolean = false;


  constructor(private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private datePipe: DatePipe, private _snackBar: MatSnackBar) { }

  ngOnInit() {
    let dates_times = new Array<DatesTimes>();
    for (let i = 0; i <=6; i++) {
      let option = new Option('', '', this.predefinedHours, [], null, false);
      let options = new Array(option);
      let dateTime = new DatesTimes(this.getDay(i), false, options, false);
      dates_times.push(dateTime);
    }

    let street = new Address('',false);
    let street_a = new Address(undefined, false, '');
    let street_b = new Address(undefined,false, undefined, '');
    let altitud = new Address(undefined,false, undefined, undefined, '');
    let city = new Address(undefined,false, undefined, undefined, undefined, '');
    let floor =  new Address(undefined,false, undefined, undefined, undefined, '', '');
    let department = new Address(undefined,false, undefined, undefined, undefined, '', '', '');
    let obser = new Address(undefined,false, undefined, undefined, undefined, '', '', '', '');
    this.addresses.push(street, street_a, street_b, altitud, city, floor, department, obser);

    let street_alt = new Address('',false);
    let street_a_alt = new Address(undefined, false, '');
    let street_b_alt = new Address(undefined,false, undefined, '');
    let altitud_alt = new Address(undefined,false, undefined, undefined, '');
    let city_alt = new Address(undefined,false, undefined, undefined, undefined, '');
    let floor_alt =  new Address(undefined,false, undefined, undefined, undefined, '', '');
    let department_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '');
    let obser_alt = new Address(undefined,false, undefined, undefined, undefined, '', '', '', '');
    this.addresses_alt.push(street_alt, street_a_alt, street_b_alt, altitud_alt, city_alt, floor_alt, department_alt, obser_alt);

    this.control_collapse_search = true;

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    this.search = new Search(this.student_name, dates_times, this.addresses, this.addresses_alt, 1, tomorrow);
    this.search.address[4].city = "La Plata";
    this.search.address_alternative[4].city = "La Plata";
    console.log(this.search);

    // this.excepciones.forEach(excep => {
    //   excep.date = tomorrow;
    // });
  }


  getDay(i: number) {
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

  searchSchedules() {

    this.control_collapse_search = false;

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

    this.setExceptionHours();

    this.cronogramaService.getCronograma(object, this.excepciones).subscribe( (response: Response) => {
      console.log(response)
      switch (response.code) {
        case 0:
          this.available_schedules = Object.values(response.data);
          this._snackBar.dismiss();
          break;
        case 2:
        case 3:{
          this.available_schedules = null;
          this.control_collapse_search = true;
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response.data
          });
        }
        break;

      }
    });

  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  onSwipeLeft(tabulator) {
    if(tabulator.selectedIndex < tabulator._tabs.length){
      tabulator.selectedIndex++;
    }
  }

  onSwipeRight(tabulator) {
    if(tabulator.selectedIndex > 0){
      tabulator.selectedIndex--;
    }
  }

  addException() {
    let rowTime = new Array<ExcepcionRowTIme>({'hour_start':'', 'hour_finish':'', 'horariosDesde': this.predefinedHours, 'horariosHasta': [], 'horariosTotales': [], 'dir_alt':false});
    let newExcepcion = {'date': new Date(), 'date_string': '', 'no_puede': false, 'horarios': rowTime};
    console.log("antes; ", this.excepciones)
    this.excepciones.push(newExcepcion);
    console.log("despues; ", this.excepciones)

  }

  removeExcepcion(excepcionIndex) {
    this.excepciones.splice(excepcionIndex, 1);
  }

  addNewRowTime(excepcionIndex){
    let rowTime: ExcepcionRowTIme = {'hour_start':'', 'hour_finish':'', 'horariosDesde': this.predefinedHours, 'horariosHasta': [], 'horariosTotales': [], 'dir_alt':false};
    this.excepciones[excepcionIndex].horarios.push(rowTime);
  }

  removeRowTime(excepcionIndex, rowRemove){
    this.excepciones[excepcionIndex].horarios.splice(rowRemove, 1);
    if (this.excepciones[excepcionIndex].horarios.length == 0) {
      this.removeExcepcion(excepcionIndex);
    }
  }

  doExcepcionHours(rowTImeIndex, excepcionIndex, rowExcepcionIndex) {
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].hour_start = this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosDesde[rowTImeIndex];
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosHasta = this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosDesde.slice(rowTImeIndex + 1);
  }

  setExceptionHourFinish(rowTImeIndex, excepcionIndex, rowExcepcionIndex) {
    this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].hour_finish = this.excepciones[excepcionIndex].horarios[rowExcepcionIndex].horariosHasta[rowTImeIndex];
  }

  setExceptionHours() {
    //Armo el array final a enviar.
    if (this.excepciones.length) {
      this.excepciones.forEach( (excepcion: Excepcion) => {
        excepcion.date_string = this.datePipe.transform(excepcion.date, 'yyyy-MM-dd');
        if (!excepcion.no_puede) {
          excepcion.horarios.forEach( (horario: ExcepcionRowTIme) => {
            horario.horariosTotales = horario.horariosDesde.filter( (hora: String) => {
              if (hora >= horario.hour_start && hora <= horario.hour_finish) {
                return true;
              }
              return false;
            })
          })
        }
      })
    }
  }

  allDay(day) {

      let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

      if (index != -1) {
        if (this.search.dates_times[index].all_day == false ) {
          this.search.dates_times[index].all_day = true;
          this.schedule_send_null = false;

          // if (this.search.dates_times[index].option.length > 1) {
          //   this.search.dates_times[index].option.splice( index, 1 );
          // } else {
          //     this.search.dates_times[index].option[0].hour_start = '';
          //     this.search.dates_times[index].option[0].hour_finish = '';
          //     this.search.dates_times[index].option[0].scheduleFrom = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
          //     this.search.dates_times[index].option[0].scheduleTo = [];
          //     this.search.dates_times[index].option[0].scheduleSend = ["08:00", "09:00", "10:00", "11:15", "12:15", "13:15", "14:30", "15:30", "16:30", "17:45", "18:45", "19:45"];
          // }
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
          this.search.dates_times[index].dir_alt = false;
          this.search.dates_times[index].option[0].scheduleSend = null;

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

    console.log('Busqueda', this.search);
  }

  allDayDA(day) {                                                                                                                                  
    // let index = this.search.dates_times.findIndex(element => { return element.name_day == day });

    // if (index != -1) {
    //   if (this.search.dates_times[index].all_day == true ) {
    //     this.search.dates_times[index].dir_alt = true;
    //     console.log('Busqueda', this.search);
    //   } else {
    //     this.search.dates_times[index].dir_alt = false;
    //     console.log('Busqueda', this.search);
    //   }
    // }
    console.log('Busqueda', this.search);
  }

  addDateTime(day) {

    if (!this.control_flag_empty) {
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

      console.log('Busqueda', this.search);

      this.control_flag_empty = true;
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

  selectionCity(city) {
    this.search.address[4].city = city;
  }

  selectionCityAlt(city) {
    this.search.address_alternative[4].city = city;
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

    console.log('Busqueda', this.search);
  }

}
