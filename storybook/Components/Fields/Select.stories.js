/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { twig } from '@sensiolabs/storybook-symfony-webpack5';
export default {
  viewMode: 'canvas',
  render: (args) => ({
    tags: ['autodocs'],
    template: twig`
            <twig:Austral:Form:Row tag="div" name="{{ name }}" labelPosition="{{ labelPosition }}" reverse="{{ reverse }}">
              ${args.labelPosition !== "external" ? '<twig:block name="label"><twig:Austral:Form:Label tag="label" entitled="{{ label }}" id="{{ id }}"/></twig:block>' : ''}
              ${args.labelPosition === "external" ? '<twig:block name="label_external"><twig:Austral:Form:Label tag="label" entitled="{{ label }}" id="{{ id }}"/></twig:block>' : ''}
              <twig:block name="field">
                <twig:Austral:Form:Select 
                  id="{{ id }}"
                  name="{{ name }}"
                  required="{{ required }}"
                  disabled="{{ disabled }}"
                  value="{{ value }}"
                  choices="{{ choices }}"
                />
              </twig:block>
              ${args.after ? '<twig:block name="after">'+args.after+'</twig:block>' : ''}
              ${args.before ? '<twig:block name="before">'+args.before+'</twig:block>' : ''}
            </twig:Austral:Form:Row>
        `
  })
};

const argTypes = {
  id: {
    name: 'ID',
    description: 'Field ID',
    control: {type: 'text', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  name: {
    name: 'Name',
    description: 'Field name',
    control: {type: 'text', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  labelPosition: {
    name: 'Label Position',
    description: 'Position for the label',
    options: ['default', "animate", 'external'],
    control: {type: 'select', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'internal'}
    }
  },
  reverse: {
    name: 'Reverse',
    description: 'Reverse label and input',
    control: {type: 'boolean'},
  },
  disabled: {
    name: 'Disabled',
    description: 'Disabled',
    control: {type: 'boolean'},
  },
  value: {
    name: 'Value',
    description: 'Field value',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  before: {
    name: 'Before',
    description: 'Before field content',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  after: {
    name: 'After',
    description: 'After field content',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  choices: {
    name: 'Choices',
    description: 'Choices values',
    control: {type: 'object'},
  },
};

export const Default = {
  tags:     ['autodocs'],
  argTypes: argTypes,
  args: {
    id: "name",
    name: "name",
    reverse: false,
    label: "Label",
    labelPosition: "default",
    disabled: false,
    required: false,
    value: "",
    choices: {
      "Value 1": "value-1",
      "Value 2": "value-2",
      "Value 3": "value-3",
      "Value 4": "value-4"
    },
    before: "",
    after: "",
  },
};