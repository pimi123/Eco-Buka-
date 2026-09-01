export interface CountryLocation {
  value: string;
  label: string;
  municipalities: string[];
}

export const deliveryCountries: CountryLocation[] = [
  {
    value: 'Kosove',
    label: 'Kosovë',
    municipalities: [
      'Prishtinë',
      'Prizren',
      'Peje',
      'Gjakovë',
      'Gjilan',
      'Ferizaj',
      'Mitrovicë',
      'Podujevë',
      'Vushtrri',
      'Suharekë',
      'Rahovec',
      'Malishevë',
      'Lipjan',
      'Fushë Kosovë',
      'Drenas',
      'Skenderaj',
      'Kamenicë',
      'Viti',
      'Klinë',
      'Istog',
      'Deçan',
      'Dragash',
      'Kaçanik',
      'Shtime',
      'Obiliq',
      'Graçanicë',
    ],
  },
  {
    value: 'Shqiperi',
    label: 'Shqipëri',
    municipalities: [
      'Tiranë',
      'Durrës',
      'Shkodër',
      'Vlorë',
      'Elbasan',
      'Fier',
      'Korçë',
      'Berat',
      'Lushnje',
      'Kavaje',
      'Lezhë',
      'Kukës',
      'Gjirokastër',
      'Sarandë',
      'Pogradec',
    ],
  },
  {
    value: 'Maqedoni e Veriut',
    label: 'Maqedoni e Veriut',
    municipalities: [
      'Shkup',
      'Tetovë',
      'Gostivar',
      'Kumanovë',
      'Strugë',
      'Oher',
      'Dibër',
      'Kërçovë',
      'Manastir',
      'Prilep',
    ],
  },
];

export function municipalitiesForCountry(country: string) {
  return deliveryCountries.find((item) => item.value === country)?.municipalities || [];
}
