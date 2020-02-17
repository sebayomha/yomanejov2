import { Component, Input, Output, EventEmitter } from '@angular/core';
declare var $:any;

@Component({
  selector: 'custom-modal',
  templateUrl: './custom-modal.component.html',
  styleUrls: ['./custom-modal.component.scss']
})

export class CustomModalComponent {

  @Output() confirmation = new EventEmitter<string>();
  @Input() data: any;
  @Input() component: string;
  
  constructor() { }

  ngOnInit() { }

  open() {
    $('#confirmModal').modal('show');
  }

  onConfirm() {
    $('#confirmModal').modal('hide');
    this.confirmation.emit(this.data);
  }
    
}